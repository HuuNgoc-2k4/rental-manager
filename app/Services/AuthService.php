<?php

namespace App\Services;

use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use App\Exceptions\BusinessException;
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
     * Chủ hộ tự đăng ký — tạo tài khoản ở trạng thái chờ quản trị viên duyệt.
     *
     * @param array $data
     * @return array{status: bool, message: string}
     */
    public function register(array $data): array
    {
        try {
            $this->model->create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'role' => UserRole::Owner->value,
                'status' => UserStatus::Pending->value
            ]);

            return ['status' => true, 'message' => 'Đăng ký thành công. Vui lòng chờ quản trị viên duyệt tài khoản.'];
        } catch (BusinessException $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error('Register user error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại.'];
        }
    }
}
