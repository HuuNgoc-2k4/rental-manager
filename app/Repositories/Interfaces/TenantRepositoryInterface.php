<?php

namespace App\Repositories\Interfaces;

use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

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

    /**
     * Hồ sơ chưa thuộc hộ nào đang ở (để chọn khi cho thuê / thêm người).
     *
     * @return Collection
     */
    public function getWithoutLiveHousehold(): Collection;

    /**
     * Hồ sơ đã gửi khai báo, chờ admin duyệt.
     *
     * @return Collection
     */
    public function getSubmitted(): Collection;

    /**
     * Hồ sơ đang gắn với một tài khoản.
     *
     * @param int $userId
     * @return Tenant|null
     */
    public function findByUserId(int $userId): ?Tenant;
}
