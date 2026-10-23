<?php

namespace App\Services;

use App\Enums\User\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthService extends BaseService
{
    /**
     * @param User $model
     */
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Đăng nhập bằng email HOẶC số điện thoại.
     *
     * @param string $login Email hoặc SĐT người dùng nhập
     * @param string $password
     * @param bool $remember
     * @return array{status: bool, message: string}
     */
    public function login(string $login, string $password, bool $remember = false): array
    {
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (!Auth::attempt([$field => $login, 'password' => $password], $remember)) {
            return ['status' => false, 'message' => 'Tài khoản hoặc mật khẩu không chính xác.'];
        }

        // Chặn tại cửa đăng nhập thay vì để vào rồi mới đẩy ra: chỉ tiết lộ trạng thái SAU khi mật khẩu đã đúng.
        if (UserStatus::isBlocked(Auth::user()->status)) {
            Auth::logout();

            return ['status' => false, 'message' => 'Tài khoản đã bị khóa, vui lòng liên hệ quản trị viên.'];
        }

        return ['status' => true, 'message' => 'Đăng nhập thành công!'];
    }

    /**
     * Đổi mật khẩu của chính mình và gỡ cờ bắt đổi mật khẩu.
     *
     * @param User $user
     * @param string $newPassword
     * @return array{status: bool, message: string}
     */
    public function changePassword(User $user, string $newPassword): array
    {
        try {
            $user->update(['password' => $newPassword, 'must_change_password' => false]);

            return ['status' => true, 'message' => 'Đã đổi mật khẩu thành công.'];
        } catch (\Exception $e) {
            Log::error('Change password error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi đổi mật khẩu.'];
        }
    }
}
