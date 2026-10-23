<?php

namespace App\Providers;

use App\Services\Manage\ApprovalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Badge số mục chờ duyệt ở sidebar (chỉ admin).
        View::composer('layouts.sidebar', function ($view) {
            $view->with('pendingApprovalCount', Auth::user()?->isAdmin() ? app(ApprovalService::class)->pendingCount() : 0);
        });
    }
}
