<?php

namespace App\Enums\User;

/**
 * Ba vai trò của hệ thống. Mỗi user giữ đúng 1 vai trò (cột users.role).
 */
enum UserRole: string
{
    case Admin = 'admin';
    case Owner = 'owner';
    case Member = 'member';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Quản trị viên',
            self::Owner => 'Chủ hộ',
            self::Member => 'Thành viên'
        };
    }

    /**
     * Class badge Tailwind cho UI.
     *
     * @return string
     */
    public function color(): string
    {
        return match ($this) {
            self::Admin => 'bg-purple-50 text-purple-700 ring-purple-600/20',
            self::Owner => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
            self::Member => 'bg-slate-50 text-slate-700 ring-slate-600/20'
        };
    }

    /**
     * @param string|null $role
     * @return bool
     */
    public static function isAdmin(?string $role = null): bool
    {
        return $role === self::Admin->value;
    }

    /**
     * @param string|null $role
     * @return bool
     */
    public static function isOwner(?string $role = null): bool
    {
        return $role === self::Owner->value;
    }

    /**
     * @param string|null $role
     * @return bool
     */
    public static function isMember(?string $role = null): bool
    {
        return $role === self::Member->value;
    }

    /**
     * Danh sách value dùng cho rule validate / dropdown.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
