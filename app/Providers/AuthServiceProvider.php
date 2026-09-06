<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Khai báo gate phân quyền cho 3 vai trò: admin / chủ hộ / thành viên.
     */
    public function boot(): void
    {
        Gate::define('is-admin', fn (User $user) => $user->isAdmin());

        // Xem dữ liệu của MỌI khu trọ (chủ hộ chỉ thấy khu của mình — lọc ở repository từng module).
        Gate::define('view-all-data', fn (User $user) => $user->canViewAllData());

        // Khu quản lý: khu trọ, phòng, khách thuê, hợp đồng, chốt số, hóa đơn, thu tiền, báo cáo.
        Gate::define('manage-rental', fn (User $user) => $user->isAdmin() || $user->isOwner());

        // Cổng thành viên: khách thuê xem hợp đồng + hóa đơn của chính mình.
        Gate::define('access-member-portal', fn (User $user) => $user->isMember());
    }
}
