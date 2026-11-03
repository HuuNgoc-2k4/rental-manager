<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Manage\ApprovalController;
use App\Http\Controllers\Manage\ContractController;
use App\Http\Controllers\Manage\ContractDocumentController;
use App\Http\Controllers\Manage\PropertyController;
use App\Http\Controllers\Manage\RoomController;
use App\Http\Controllers\Manage\TenantController;
use App\Http\Controllers\Member\ContractController as MemberContractController;
use App\Http\Controllers\Member\HouseholdController;
use App\Http\Controllers\Member\ProfileController;
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
            Route::post('/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('reset-password');
        });
    });

    /**
     * Khu quản lý nhà trọ — chỉ admin (chủ nhà)
     */
    Route::middleware('can:manage-rental')->prefix('manage')->name('manage.')->group(function () {
        Route::prefix('properties')->name('properties.')->group(function () {
            Route::post('/', [PropertyController::class, 'store'])->name('store');
            Route::put('/{property}', [PropertyController::class, 'update'])->name('update');
            Route::delete('/{property}', [PropertyController::class, 'destroy'])->name('destroy');
            Route::post('/{property}/rooms', [RoomController::class, 'store'])->name('rooms.store');
        });

        Route::prefix('tenants')->name('tenants.')->group(function () {
            Route::get('/', [TenantController::class, 'index'])->name('index');
            Route::post('/', [TenantController::class, 'store'])->name('store');
            Route::put('/{tenant}', [TenantController::class, 'update'])->name('update');
            Route::delete('/{tenant}', [TenantController::class, 'destroy'])->name('destroy');
            Route::post('/{tenant}/account', [ContractController::class, 'issueAccount'])->name('account');
        });

        Route::post('rooms/{room}/rent', [ContractController::class, 'store'])->name('rooms.rent');
        Route::prefix('contracts')->name('contracts.')->group(function () {
            Route::post('/{contract}/document', [ContractDocumentController::class, 'store'])->name('documents.store');
            Route::prefix('documents/{document}')->name('documents.')->group(function () {
                Route::get('/', [ContractDocumentController::class, 'show'])->name('show');
                Route::get('/edit', [ContractDocumentController::class, 'edit'])->name('edit');
                Route::put('/', [ContractDocumentController::class, 'update'])->name('update');
                Route::post('/refresh', [ContractDocumentController::class, 'refresh'])->name('refresh');
                Route::post('/submit', [ContractDocumentController::class, 'submit'])->name('submit');
                Route::get('/pdf', [ContractDocumentController::class, 'pdf'])->name('pdf');
                Route::get('/word', [ContractDocumentController::class, 'word'])->name('word');
            });
            Route::post('/{contract}/members', [ContractController::class, 'addMember'])->name('members.store');
            Route::post('/{contract}/checkout', [ContractController::class, 'checkout'])->name('checkout');
        });
        Route::prefix('approvals')->name('approvals.')->group(function () {
            Route::post('/members/{link}/approve', [ApprovalController::class, 'approveMember'])->name('members.approve');
            Route::post('/members/{link}/reject', [ApprovalController::class, 'rejectMember'])->name('members.reject');
            Route::post('/declarations/{tenant}/verify', [ApprovalController::class, 'verifyDeclaration'])->name('declarations.verify');
            Route::post('/declarations/{tenant}/return', [ApprovalController::class, 'returnDeclaration'])->name('declarations.return');
        });

        Route::delete('members/{link}', [ContractController::class, 'leave'])->name('members.leave');

        Route::prefix('rooms')->name('rooms.')->group(function () {
            Route::get('/', [RoomController::class, 'index'])->name('index');
            Route::get('/{room}', [RoomController::class, 'show'])->name('show');
            Route::put('/{room}', [RoomController::class, 'update'])->name('update');
            Route::put('/{room}/furniture', [RoomController::class, 'updateFurniture'])->name('furniture');
            Route::patch('/{room}/status', [RoomController::class, 'updateStatus'])->name('status');
            Route::post('/{room}/meters/replace', [RoomController::class, 'replaceMeter'])->name('meters.replace');
            Route::delete('/{room}', [RoomController::class, 'destroy'])->name('destroy');
        });
    });

    /**
     * Cổng hộ thuê — chủ hộ + thành viên xem phòng, hồ sơ của mình
     */
    Route::middleware('can:access-member-portal')->prefix('member')->name('member.')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/contract', [MemberContractController::class, 'show'])->name('contract.show');

        Route::middleware('can:manage-household')->prefix('household')->name('household.')->group(function () {
            Route::post('/members', [HouseholdController::class, 'storeMember'])->name('members.store');
            Route::get('/members/{tenant}/edit', [ProfileController::class, 'editMember'])->name('members.edit');
            Route::put('/members/{tenant}', [ProfileController::class, 'updateMember'])->name('members.update');
            Route::post('/members/{tenant}/account', [HouseholdController::class, 'issueAccount'])->name('members.account');
            Route::post('/members/{tenant}/reset-password', [HouseholdController::class, 'resetPassword'])->name('members.reset-password');
            Route::post('/contract/approve', [MemberContractController::class, 'approve'])->name('contract.approve');
            Route::post('/contract/reject', [MemberContractController::class, 'reject'])->name('contract.reject');
        });
    });
});
