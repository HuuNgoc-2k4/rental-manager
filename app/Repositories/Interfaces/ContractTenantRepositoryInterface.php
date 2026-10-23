<?php

namespace App\Repositories\Interfaces;

use App\Models\Contract;
use App\Models\ContractTenant;
use Illuminate\Database\Eloquent\Collection;

interface ContractTenantRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Người đang nằm trong hộ (chưa rời, kể cả chờ duyệt) kèm hồ sơ và tài khoản.
     *
     * @param Contract $contract
     * @return Collection
     */
    public function getLiveLinks(Contract $contract): Collection;

    /**
     * Dòng hộ đang hiệu lực của một khách (chưa rời, hợp đồng còn hiệu lực), null nếu khách không ở hộ nào.
     *
     * @param int $tenantId
     * @return ContractTenant|null
     */
    public function findLiveLinkForTenant(int $tenantId): ?ContractTenant;

    /**
     * Dòng bất kỳ (kể cả đã rời / bị từ chối) của khách trong hợp đồng — unique(contract_id, tenant_id) nên phải mở lại thay vì chèn mới.
     *
     * @param int $contractId
     * @param int $tenantId
     * @return ContractTenant|null
     */
    public function findAnyLink(int $contractId, int $tenantId): ?ContractTenant;

    /**
     * Số người trong hộ chưa rời.
     *
     * @param Contract $contract
     * @param bool $includePending false = chỉ tính người đã duyệt
     * @return int
     */
    public function countLive(Contract $contract, bool $includePending): int;

    /**
     * Dòng hộ đang ở của người đăng nhập (users → tenants.user_id → contract_tenants chưa rời, hợp đồng hiệu lực).
     * Đây là nguồn DUY NHẤT xác định "hộ của tôi"; không bao giờ lấy contract_id từ request.
     *
     * @param int $userId
     * @return ContractTenant|null
     */
    public function findLiveLinkForUser(int $userId): ?ContractTenant;

    /**
     * Người được thêm vào hộ đang chờ admin duyệt.
     *
     * @return Collection
     */
    public function getPending(): Collection;
}
