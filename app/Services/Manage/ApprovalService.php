<?php

namespace App\Services\Manage;

use App\Enums\Contract\ContractStatus;
use App\Enums\Contract\MemberApproval;
use App\Enums\Tenant\TenantInfoStatus;
use App\Exceptions\BusinessException;
use App\Models\Contract;
use App\Models\ContractTenant;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Interfaces\ContractRepositoryInterface;
use App\Repositories\Interfaces\ContractTenantRepositoryInterface;
use App\Repositories\Interfaces\RoomRepositoryInterface;
use App\Repositories\Interfaces\TenantRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalService extends BaseService
{
    /**
     * @param Contract $model
     * @param ContractTenantRepositoryInterface $linkRepository
     * @param ContractRepositoryInterface $contractRepository
     * @param RoomRepositoryInterface $roomRepository
     * @param TenantRepositoryInterface $tenantRepository
     */
    public function __construct(
        Contract $model,
        protected ContractTenantRepositoryInterface $linkRepository,
        protected ContractRepositoryInterface $contractRepository,
        protected RoomRepositoryInterface $roomRepository,
        protected TenantRepositoryInterface $tenantRepository
    ) {
        parent::__construct($model);
    }

    /**
     * @return Collection
     */
    public function getPendingMembers(): Collection
    {
        return $this->linkRepository->getPending();
    }

    /**
     * @return int
     */
    public function pendingCount(): int
    {
        return $this->linkRepository->getPending()->count() + $this->tenantRepository->getSubmitted()->count();
    }

    /**
     * Khai báo thông tin chờ duyệt.
     *
     * @return Collection
     */
    public function getSubmittedDeclarations(): Collection
    {
        return $this->tenantRepository->getSubmitted();
    }

    /**
     * Xác minh khai báo: từ đây các trường định danh bị khóa.
     *
     * @param Tenant $tenant
     * @param User $actor
     * @return array{status: bool, message: string}
     */
    public function verifyDeclaration(Tenant $tenant, User $actor): array
    {
        try {
            if (!TenantInfoStatus::isSubmitted($tenant->info_status)) {
                throw new BusinessException('Khai báo này không còn ở trạng thái chờ duyệt.', 'not_submitted');
            }

            $this->tenantRepository->update($tenant, [
                'info_status' => TenantInfoStatus::Verified->value,
                'info_reject_reason' => null,
                'info_verified_at' => now(),
                'info_verified_by' => $actor->id
            ]);

            return ['status' => true, 'message' => "Đã xác minh thông tin của {$tenant->full_name}."];
        } catch (BusinessException $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error('Verify declaration error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi xác minh khai báo.'];
        }
    }

    /**
     * Trả lại khai báo, bắt buộc có lý do để khách sửa và gửi lại.
     *
     * @param Tenant $tenant
     * @param string $reason
     * @return array{status: bool, message: string}
     */
    public function returnDeclaration(Tenant $tenant, string $reason): array
    {
        try {
            if (!TenantInfoStatus::isSubmitted($tenant->info_status)) {
                throw new BusinessException('Khai báo này không còn ở trạng thái chờ duyệt.', 'not_submitted');
            }

            $this->tenantRepository->update($tenant, [
                'info_status' => TenantInfoStatus::Rejected->value,
                'info_reject_reason' => $reason
            ]);

            return ['status' => true, 'message' => "Đã trả lại khai báo của {$tenant->full_name}."];
        } catch (BusinessException $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error('Return declaration error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi trả lại khai báo.'];
        }
    }

    /**
     * Duyệt người ở cùng; kiểm lại max_occupants (chỉ tính người đã duyệt cộng người này).
     *
     * @param ContractTenant $link
     * @return array{status: bool, message: string}
     */
    public function approveMember(ContractTenant $link): array
    {
        try {
            DB::beginTransaction();

            $contract = $this->assertPending($link);
            $room = $this->roomRepository->findOrFail($contract->room_id);

            if ($this->linkRepository->countLive($contract, false) + 1 > $room->max_occupants) {
                throw new BusinessException("Phòng {$room->name} đã đủ {$room->max_occupants} người tối đa.", 'room_full');
            }

            $this->linkRepository->update($link, ['approval_status' => MemberApproval::Approved->value, 'reject_reason' => null]);
            DB::commit();

            return ['status' => true, 'message' => 'Đã duyệt người ở cùng.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Approve member error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi duyệt người ở cùng.'];
        }
    }

    /**
     * Từ chối: bắt buộc có lý do; dòng bị đóng (left_at) để không còn tính là đang ở nhưng vẫn lưu lịch sử.
     *
     * @param ContractTenant $link
     * @param string $reason
     * @return array{status: bool, message: string}
     */
    public function rejectMember(ContractTenant $link, string $reason): array
    {
        try {
            DB::beginTransaction();

            $this->assertPending($link);
            $this->linkRepository->update($link, [
                'approval_status' => MemberApproval::Rejected->value,
                'reject_reason' => $reason,
                'left_at' => now()->toDateString()
            ]);
            DB::commit();

            return ['status' => true, 'message' => 'Đã từ chối yêu cầu thêm người.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reject member error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi từ chối yêu cầu.'];
        }
    }

    /**
     * @param ContractTenant $link
     * @return Contract
     *
     * @throws BusinessException
     */
    private function assertPending(ContractTenant $link): Contract
    {
        $contract = $this->contractRepository->findOrFail($link->contract_id);

        if ($link->left_at || !MemberApproval::isPending($link->approval_status) || !ContractStatus::isActive($contract->status)) {
            throw new BusinessException('Yêu cầu này không còn ở trạng thái chờ duyệt.', 'not_pending');
        }

        return $contract;
    }
}
