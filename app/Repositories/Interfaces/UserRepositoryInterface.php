<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator;

    /**
     * @param string $login Email hoặc số điện thoại
     * @return User|null
     */
    public function findByLogin(string $login): ?User;

    /**
     * Tài khoản chờ duyệt (chủ hộ tự đăng ký) — hiện ở đầu trang quản lý user.
     *
     * @return Collection
     */
    public function getPending(): Collection;
}
