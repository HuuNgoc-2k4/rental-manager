<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Manage\PropertyController;
use App\Http\Controllers\Manage\RoomController;
use App\Http\Controllers\Manage\TenantController;
use App\Http\Controllers\TenantImageController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware(['auth', 'check.status', 'password.changed'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::get('/tenants/{tenant}/id-image/{side}', [TenantImageController::class, 'show'])->whereIn('side', ['front', 'back'])->name('tenants.id-image');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    /**
     * Quản trị hệ thống — chỉ admin
     */
    Route::middleware('can:is-admin')->prefix('admin')->name('admin.')->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::patch('/{user}/status', [AdminUserController::class, 'updateStatus'])->name('status');
        });
    });

    /**
     * Khu quản lý nhà trọ — chỉ admin (chủ nhà)
     */
    Route::middleware('can:manage-rental')->prefix('manage')->name('manage.')->group(function () {
        Route::prefix('properties')->name('properties.')->group(function () {
            Route::get('/', [PropertyController::class, 'index'])->name('index');
            Route::post('/', [PropertyController::class, 'store'])->name('store');
            Route::get('/{property}', [PropertyController::class, 'show'])->name('show');
            Route::put('/{property}', [PropertyController::class, 'update'])->name('update');
            Route::delete('/{property}', [PropertyController::class, 'destroy'])->name('destroy');
            Route::post('/{property}/rooms', [RoomController::class, 'store'])->name('rooms.store');
        });

        Route::prefix('tenants')->name('tenants.')->group(function () {
            Route::get('/', [TenantController::class, 'index'])->name('index');
            Route::post('/', [TenantController::class, 'store'])->name('store');
            Route::put('/{tenant}', [TenantController::class, 'update'])->name('update');
            Route::delete('/{tenant}', [TenantController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('rooms')->name('rooms.')->group(function () {
            Route::put('/{room}', [RoomController::class, 'update'])->name('update');
            Route::patch('/{room}/status', [RoomController::class, 'updateStatus'])->name('status');
            Route::post('/{room}/meters/replace', [RoomController::class, 'replaceMeter'])->name('meters.replace');
            Route::delete('/{room}', [RoomController::class, 'destroy'])->name('destroy');
        });
    });

    /**
     * Cổng hộ thuê — chủ hộ + thành viên xem phòng, hồ sơ của mình
     */
    Route::middleware('can:access-member-portal')->prefix('member')->name('member.')->group(function () {
        //
    });
});
