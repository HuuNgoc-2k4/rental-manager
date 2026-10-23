<?php

namespace App\Services\Manage;

use App\Enums\Contract\ContractStatus;
use App\Enums\Contract\MemberApproval;
use App\Enums\Property\RoomStatus;
use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use App\Exceptions\BusinessException;
use App\Models\Contract;
use App\Models\ContractTenant;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Interfaces\ContractRepositoryInterface;
use App\Repositories\Interfaces\ContractTenantRepositoryInterface;
use App\Repositories\Interfaces\RoomRepositoryInterface;
use App\Repositories\Interfaces\TenantRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\AccountService;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Hộ thuê = hợp đồng (khung T1). Giữ các bất biến: 1 hợp đồng hiệu lực / phòng, 1 người chỉ ở 1 hộ,
 * đúng 1 chủ hộ, số người ≤ max_occupants.
 */
class ContractService extends BaseService
{
    public function __construct(
        Contract $model,
        protected ContractRepositoryInterface $contractRepository,
        protected ContractTenantRepositoryInterface $linkRepository,
        protected RoomRepositoryInterface $roomRepository,
        protected TenantRepositoryInterface $tenantRepository,
        protected UserRepositoryInterface $userRepository,
        protected TenantService $tenantService,
        protected AccountService $accountService
    ) {
        parent::__construct($model);
    }

    /**
     * Cho thuê phòng: tạo hợp đồng, đặt chủ hộ, chuyển phòng sang Đang thuê, tùy chọn cấp tài khoản chủ hộ.
     *
     * @param Room $room
     * @param array $data tenant_id | full_name + phone, start_date, end_date, note, issue_account
     * @param User $actor
     * @return array{status: bool, message: string, credentials?: array}
     */
    public function rentOut(Room $room, array $data, User $actor): array
    {
        try {
            DB::beginTransaction();

            if (!RoomStatus::isVacant($room->status) || $this->roomRepository->hasActiveContract($room)) {
                throw new BusinessException('Phòng này không ở trạng thái trống nên không thể cho thuê.', 'room_not_vacant');
            }

            $tenant = $this->resolveTenant($data, $actor);
            $this->assertNotInHousehold($tenant);

            $contract = $this->contractRepository->create([
                'room_id' => $room->id,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'status' => ContractStatus::Active->value,
                'note' => $data['note'] ?? null
            ]);

            $this->linkRepository->create([
                'contract_id' => $contract->id,
                'tenant_id' => $tenant->id,
                'is_primary' => true,
                'approval_status' => MemberApproval::Approved->value,
                'joined_at' => $data['start_date'],
                'created_by' => $actor->id
            ]);

            $this->roomRepository->update($room, ['status' => RoomStatus::Occupied->value]);

            $result = ['status' => true, 'message' => "Đã cho {$tenant->full_name} thuê phòng {$room->name}."];
            if (!empty($data['issue_account'])) {
                $result['credentials'] = $this->accountService->issueForTenant($tenant, UserRole::Owner);
            }

            DB::commit();

            return $result;
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Rent out room error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi cho thuê phòng.'];
        }
    }

    /**
     * Thêm người ở cùng. Admin thêm thì duyệt luôn; chủ hộ thêm thì chờ admin duyệt.
     * Chặn ngay khi số người (kể cả đang chờ duyệt) đã chạm max_occupants.
     *
     * @param Contract $contract
     * @param array $data tenant_id | full_name + phone (+ thông tin khai hộ)
     * @param User $actor
     * @param array $files
     * @return array{status: bool, message: string}
     */
    public function addMember(Contract $contract, array $data, User $actor, array $files = []): array
    {
        try {
            DB::beginTransaction();

            if (!ContractStatus::isActive($contract->status)) {
                throw new BusinessException('Hợp đồng đã kết thúc, không thể thêm người.', 'contract_ended');
            }

            $room = $this->roomRepository->findOrFail($contract->room_id);
            if ($this->linkRepository->countLive($contract, true) >= $room->max_occupants) {
                throw new BusinessException("Phòng {$room->name} đã đủ {$room->max_occupants} người tối đa.", 'room_full');
            }

            $tenant = $this->resolveTenant($data, $actor, $files);
            $this->assertNotInHousehold($tenant);

            $approved = $actor->isAdmin();
            $this->linkRepository->create([
                'contract_id' => $contract->id,
                'tenant_id' => $tenant->id,
                'is_primary' => false,
                'approval_status' => ($approved ? MemberApproval::Approved : MemberApproval::Pending)->value,
                'joined_at' => now()->toDateString(),
                'created_by' => $actor->id
            ]);

            DB::commit();

            return [
                'status' => true,
                'message' => $approved ? "Đã thêm {$tenant->full_name} vào phòng." : "Đã gửi yêu cầu thêm {$tenant->full_name}, chờ chủ nhà duyệt."
            ];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Add member error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi thêm người ở cùng.'];
        }
    }

    /**
     * Thành viên rời đi: đặt left_at và khóa tài khoản vì không còn quan hệ thuê. Chủ hộ không rời được.
     *
     * @param ContractTenant $link
     * @return array{status: bool, message: string}
     */
    public function leave(ContractTenant $link): array
    {
        try {
            DB::beginTransaction();

            if ($link->left_at) {
                throw new BusinessException('Người này đã rời khỏi phòng.', 'already_left');
            }
            if ($link->is_primary) {
                throw new BusinessException('Chủ hộ không thể rời đi. Hãy trả phòng để kết thúc hợp đồng.', 'primary_cannot_leave');
            }

            $this->linkRepository->update($link, ['left_at' => now()->toDateString()]);

            $tenant = $this->tenantRepository->findOrFail($link->tenant_id);
            if ($tenant->user_id) {
                $user = $this->userRepository->findOrFail($tenant->user_id);
                if (!$user->isAdmin()) {
                    $this->userRepository->update($user, ['status' => UserStatus::Blocked->value]);
                }
            }

            DB::commit();

            return ['status' => true, 'message' => "{$tenant->full_name} đã rời phòng, tài khoản (nếu có) đã bị khóa."];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Leave household error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi cập nhật người rời phòng.'];
        }
    }

    /**
     * Trả phòng: kết thúc hợp đồng, đóng mọi dòng đang ở, phòng về Trống. Tài khoản cả hộ vẫn mở
     * để xem công nợ còn lại (khóa thủ công sau khi tất toán).
     *
     * @param Contract $contract
     * @param string $endedAt Y-m-d
     * @return array{status: bool, message: string}
     */
    public function checkout(Contract $contract, string $endedAt): array
    {
        try {
            DB::beginTransaction();

            if (!ContractStatus::isActive($contract->status)) {
                throw new BusinessException('Hợp đồng này đã kết thúc.', 'contract_ended');
            }
            if ($endedAt < $contract->start_date->toDateString()) {
                throw new BusinessException('Ngày trả phòng không được trước ngày vào ở.', 'invalid_end_date');
            }

            foreach ($this->linkRepository->getLiveLinks($contract) as $link) {
                $this->linkRepository->update($link, ['left_at' => $endedAt]);
            }

            $this->contractRepository->update($contract, ['status' => ContractStatus::Ended->value, 'ended_at' => $endedAt]);
            $this->roomRepository->update($this->roomRepository->findOrFail($contract->room_id), ['status' => RoomStatus::Vacant->value]);

            DB::commit();

            return ['status' => true, 'message' => 'Đã trả phòng, phòng chuyển về trạng thái trống.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi trả phòng.'];
        }
    }

    /**
     * Cấp tài khoản cho người đã được duyệt trong hộ; vai trò theo vị trí (chủ hộ = owner, còn lại = member).
     *
     * @param Tenant $tenant
     * @return array{status: bool, message: string, credentials?: array}
     */
    public function issueAccount(Tenant $tenant): array
    {
        try {
            DB::beginTransaction();

            $link = $this->linkRepository->findLiveLinkForTenant($tenant->id);
            if (!$link || !MemberApproval::isApproved($link->approval_status)) {
                throw new BusinessException('Chỉ cấp tài khoản cho người đã được duyệt và đang ở trong một phòng.', 'not_in_household');
            }

            $credentials = $this->accountService->issueForTenant($tenant, $link->is_primary ? UserRole::Owner : UserRole::Member);
            DB::commit();

            return ['status' => true, 'message' => "Đã cấp tài khoản cho {$tenant->full_name}.", 'credentials' => $credentials];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Issue account error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi cấp tài khoản.'];
        }
    }

    /**
     * Khách có sẵn (chỉ admin được chọn) hoặc tạo hồ sơ mới từ dữ liệu form.
     *
     * @param array $data
     * @param User $actor
     * @param array $files
     * @return Tenant
     *
     * @throws BusinessException
     */
    private function resolveTenant(array $data, User $actor, array $files = []): Tenant
    {
        if (!empty($data['tenant_id'])) {
            if (!$actor->isAdmin()) {
                throw new BusinessException('Bạn chỉ có thể thêm người mới, không chọn hồ sơ có sẵn.', 'forbidden_existing');
            }

            return $this->tenantRepository->findOrFail((int) $data['tenant_id']);
        }

        return $this->tenantService->createProfile($data, $files, $actor);
    }

    /**
     * @param Tenant $tenant
     * @return void
     *
     * @throws BusinessException
     */
    private function assertNotInHousehold(Tenant $tenant): void
    {
        if ($this->linkRepository->findLiveLinkForTenant($tenant->id)) {
            throw new BusinessException("{$tenant->full_name} đang thuộc một hộ khác.", 'tenant_in_household');
        }
    }
}
