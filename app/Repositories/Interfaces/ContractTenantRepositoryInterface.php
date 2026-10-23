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
     * Số người trong hộ chưa rời.
     *
     * @param Contract $contract
     * @param bool $includePending false = chỉ tính người đã duyệt
     * @return int
     */
    public function countLive(Contract $contract, bool $includePending): int;
}
