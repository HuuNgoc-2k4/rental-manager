<?php

namespace App\Services;

use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use App\Exceptions\BusinessException;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\Interfaces\TenantRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;

/**
 * Cấp tài khoản cho hồ sơ khách và đặt lại mật khẩu bằng mật khẩu tạm (hiện đúng 1 lần).
 * Không tự mở transaction: luôn chạy trong transaction của service gọi nó.
 */
class AccountService extends BaseService
{
    protected UserRepositoryInterface $userRepository;

    protected TenantRepositoryInterface $tenantRepository;

    /**
     * @param User $model
     * @param UserRepositoryInterface $userRepository
     * @param TenantRepositoryInterface $tenantRepository
     */
    public function __construct(User $model, UserRepositoryInterface $userRepository, TenantRepositoryInterface $tenantRepository)
    {
        parent::__construct($model);
        $this->userRepository = $userRepository;
        $this->tenantRepository = $tenantRepository;
    }

    /**
     * Cấp tài khoản với vai trò theo vị trí trong hộ.
     * - Hồ sơ đã có tài khoản: dùng lại, đổi vai trò, mở khóa, KHÔNG đổi mật khẩu.
     * - SĐT đã có user (khách thuê lại): dùng lại user đó, đặt mật khẩu tạm mới.
     * - Còn lại: tạo user mới.
     *
     * @param Tenant $tenant
     * @param UserRole $role owner hoặc member
     * @return array{name: string, login: string|null, password: string|null} password null = giữ mật khẩu cũ
     *
     * @throws BusinessException
     */
    public function issueForTenant(Tenant $tenant, UserRole $role): array
    {
        if ($tenant->user_id) {
            $user = $this->userRepository->findOrFail($tenant->user_id);
            $this->assertNotAdmin($user);
            $this->userRepository->update($user, ['role' => $role->value, 'status' => UserStatus::Active->value]);

            return ['name' => $tenant->full_name, 'login' => $user->phone ?? $user->email, 'password' => null];
        }

        if (!$tenant->phone) {
            throw new BusinessException('Cần có số điện thoại của khách để cấp tài khoản.', 'phone_required');
        }

        $existing = $this->userRepository->findByConditions([['phone', '=', $tenant->phone]])->first();
        $password = $this->generateTempPassword();

        if ($existing) {
            $this->assertNotAdmin($existing);
            if ($this->tenantRepository->findByUserId($existing->id)) {
                throw new BusinessException('Số điện thoại này đang gắn với hồ sơ khách khác.', 'phone_in_use');
            }

            $this->userRepository->update($existing, [
                'role' => $role->value,
                'status' => UserStatus::Active->value,
                'password' => $password,
                'must_change_password' => true
            ]);
            $user = $existing;
        } else {
            $user = $this->userRepository->create([
                'name' => $tenant->full_name,
                'phone' => $tenant->phone,
                'password' => $password,
                'role' => $role->value,
                'status' => UserStatus::Active->value,
                'must_change_password' => true
            ]);
        }

        $this->tenantRepository->update($tenant, ['user_id' => $user->id]);

        return ['name' => $tenant->full_name, 'login' => $user->phone, 'password' => $password];
    }

    /**
     * Sinh mật khẩu tạm mới và bắt đổi ở lần đăng nhập tới.
     *
     * @param User $user
     * @return array{name: string, login: string|null, password: string}
     */
    public function resetPassword(User $user): array
    {
        $password = $this->generateTempPassword();
        $this->userRepository->update($user, ['password' => $password, 'must_change_password' => true]);

        return ['name' => $user->name, 'login' => $user->phone ?? $user->email, 'password' => $password];
    }

    /**
     * 8 ký tự, bỏ các ký tự dễ nhầm (0/O, 1/l/I) vì mật khẩu được đọc / nhắn qua Zalo.
     *
     * @return string
     */
    public function generateTempPassword(): string
    {
        $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $password = '';

        for ($i = 0; $i < 8; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $password;
    }

    /**
     * @param User $user
     * @return void
     *
     * @throws BusinessException
     */
    private function assertNotAdmin(User $user): void
    {
        if ($user->isAdmin()) {
            throw new BusinessException('Số điện thoại này thuộc tài khoản quản trị, không thể dùng cho khách thuê.', 'phone_is_admin');
        }
    }
}
