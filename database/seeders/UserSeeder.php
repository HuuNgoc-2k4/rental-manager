<?php

namespace Database\Seeders;

use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Tài khoản mẫu cho 3 vai trò — mật khẩu chung: password
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Quản trị viên', 'email' => 'admin@gmail.com', 'phone' => '0987654321', 'role' => UserRole::Admin],
            ['name' => 'Chủ hộ Nguyễn Văn A', 'email' => 'owner@gmail.com', 'phone' => '0987654322', 'role' => UserRole::Owner],
            ['name' => 'Thành viên Trần Thị B', 'email' => 'member@gmail.com', 'phone' => '0987654323', 'role' => UserRole::Member]
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'phone' => $account['phone'],
                    'password' => 'password',
                    'role' => $account['role']->value,
                    'status' => UserStatus::Active->value
                ]
            );
        }
    }
}
