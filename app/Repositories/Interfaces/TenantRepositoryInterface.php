<?php

namespace App\Repositories\Interfaces;

use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TenantRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param array $filters keyword (tên / SĐT / CCCD), info_status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator;

    /**
     * Khách từng nằm trong bất kỳ hợp đồng nào (kể cả đã kết thúc).
     *
     * @param Tenant $tenant
     * @return bool
     */
    public function hasContractHistory(Tenant $tenant): bool;
}
