<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Khai báo gate phân quyền cho 3 vai trò: admin (chủ nhà) / chủ hộ (người thuê đứng hợp đồng) / thành viên (người ở cùng).
     */
    public function boot(): void
    {
        Gate::define('is-admin', fn (User $user) => $user->isAdmin());

        // Khu quản lý: khu trọ, phòng, khách thuê, hợp đồng, duyệt — chỉ chủ nhà (admin).
        Gate::define('manage-rental', fn (User $user) => $user->isAdmin());

        // Cổng hộ thuê: chủ hộ (người đứng hợp đồng) và thành viên ở cùng xem phòng của mình.
        Gate::define('access-member-portal', fn (User $user) => $user->isOwner() || $user->isMember());

        // Chủ hộ quản lý hộ của mình: thêm người ở cùng, cấp tài khoản, đặt lại mật khẩu thành viên.
        Gate::define('manage-household', fn (User $user) => $user->isOwner());
    }
}
