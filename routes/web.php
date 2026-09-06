<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
});

Route::middleware(['auth', 'check.status'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /**
     * Quản trị hệ thống — chỉ admin
     */
    Route::middleware('can:is-admin')->prefix('admin')->name('admin.')->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::patch('/{user}/status', [AdminUserController::class, 'updateStatus'])->name('status');
        });
    });

    /**
     * Khu quản lý nhà trọ — admin + chủ hộ (các module 01-08 sẽ khai báo route ở đây)
     */
    Route::middleware('can:manage-rental')->prefix('manage')->name('manage.')->group(function () {
        //
    });

    /**
     * Cổng thành viên — khách thuê xem hợp đồng + hóa đơn của mình
     */
    Route::middleware('can:access-member-portal')->prefix('member')->name('member.')->group(function () {
        //
    });
});
