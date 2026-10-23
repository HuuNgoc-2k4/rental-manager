<?php

namespace App\Services\Member;

use App\Exceptions\BusinessException;
use App\Models\Contract;
use App\Models\ContractTenant;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Interfaces\ContractTenantRepositoryInterface;
use App\Repositories\Interfaces\RoomRepositoryInterface;
use App\Repositories\Interfaces\TenantRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\AccountService;
use App\Services\BaseService;
use App\Services\Manage\ContractService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Cổng chủ hộ / thành viên. Hộ hiện tại LUÔN suy ra từ user đăng nhập (findLiveLinkForUser),
 * không method nào nhận contract_id từ request — đây là rào chặn IDOR giữa các phòng.
 */
class HouseholdService extends BaseService
{
    public function __construct(
        Contract $model,
        protected ContractTenantRepositoryInterface $linkRepository,
        protected RoomRepositoryInterface $roomRepository,
        protected TenantRepositoryInterface $tenantRepository,
        protected UserRepositoryInterface $userRepository,
        protected ContractService $contractService,
        protected AccountService $accountService
    ) {
        parent::__construct($model);
    }

    /**
     * Dữ liệu trang "Phòng của tôi"; null nếu user chưa thuộc phòng nào.
     *
     * @param User $user
     * @return array{link: ContractTenant, contract: Contract, room: Room, links: Collection, isOwner: bool}|null
     */
    public function getPortal(User $user): ?array
    {
        $link = $this->linkRepository->findLiveLinkForUser($user->id);
        if (!$link) {
            return null;
        }

        $contract = $link->contract;
        $room = $this->roomRepository->findOrFail($contract->room_id)->load('property');

        return [
            'link' => $link,
            'contract' => $contract,
            'room' => $room,
            'links' => $this->linkRepository->getLiveLinks($contract),
            'isOwner' => $link->is_primary
        ];
    }

    /**
     * Chủ hộ thêm người ở cùng (chờ admin duyệt). Chỉ tạo hồ sơ mới, không chọn hồ sơ có sẵn.
     *
     * @param User $owner
     * @param array $data
     * @param array $files
     * @return array{status: bool, message: string}
     */
    public function addMember(User $owner, array $data, array $files = []): array
    {
        $link = $this->linkRepository->findLiveLinkForUser($owner->id);
        if (!$link || !$link->is_primary) {
            return ['status' => false, 'message' => 'Chỉ chủ hộ mới được thêm người ở cùng.'];
        }

        unset($data['tenant_id']);

        return $this->contractService->addMember($link->contract, $data, $owner, $files);
    }

    /**
     * @param User $owner
     * @param Tenant $tenant
     * @return array{status: bool, message: string, credentials?: array}
     */
    public function issueMemberAccount(User $owner, Tenant $tenant): array
    {
        $this->assertMemberOfMyHousehold($owner, $tenant);

        return $this->contractService->issueAccount($tenant);
    }

    /**
     * @param User $owner
     * @param Tenant $tenant
     * @return array{status: bool, message: string, credentials?: array}
     */
    public function resetMemberPassword(User $owner, Tenant $tenant): array
    {
        $this->assertMemberOfMyHousehold($owner, $tenant);

        try {
            DB::beginTransaction();

            if (!$tenant->user_id) {
                throw new BusinessException('Người này chưa có tài khoản để đặt lại mật khẩu.', 'no_account');
            }

            $credentials = $this->accountService->resetPassword($this->userRepository->findOrFail($tenant->user_id));
            DB::commit();

            return ['status' => true, 'message' => "Đã đặt lại mật khẩu cho {$tenant->full_name}.", 'credentials' => $credentials];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reset member password error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi đặt lại mật khẩu.'];
        }
    }

    /**
     * Tenant phải là THÀNH VIÊN (không phải chính chủ hộ) trong hộ đang ở của chủ hộ đăng nhập.
     * Sai hộ → 404 để không lộ hồ sơ của hộ khác tồn tại.
     *
     * @param User $owner
     * @param Tenant $tenant
     * @return ContractTenant dòng của tenant trong hộ
     *
     * @throws ModelNotFoundException
     */
    public function assertMemberOfMyHousehold(User $owner, Tenant $tenant): ContractTenant
    {
        $mine = $this->linkRepository->findLiveLinkForUser($owner->id);
        $theirs = $this->linkRepository->findLiveLinkForTenant($tenant->id);

        if (!$mine || !$mine->is_primary || !$theirs || $theirs->contract_id !== $mine->contract_id || $theirs->is_primary) {
            throw (new ModelNotFoundException)->setModel(Tenant::class, [$tenant->id]);
        }

        return $theirs;
    }
}
