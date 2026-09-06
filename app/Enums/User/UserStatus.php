<?php

namespace App\Enums\User;

enum UserStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Blocked = 'blocked';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Chờ duyệt',
            self::Active => 'Đang hoạt động',
            self::Blocked => 'Đã khóa'
        };
    }

    /**
     * @return string
     */
    public function color(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::Active => 'bg-green-50 text-green-700 ring-green-600/20',
            self::Blocked => 'bg-rose-50 text-rose-700 ring-rose-600/20'
        };
    }

    /**
     * @return string
     */
    public function iconColor(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-600',
            self::Active => 'bg-green-600',
            self::Blocked => 'bg-rose-600'
        };
    }

    /**
     * @param string|null $status
     * @return bool
     */
    public static function isPending(?string $status = null): bool
    {
        return $status === self::Pending->value;
    }

    /**
     * @param string|null $status
     * @return bool
     */
    public static function isActive(?string $status = null): bool
    {
        return $status === self::Active->value;
    }

    /**
     * @param string|null $status
     * @return bool
     */
    public static function isBlocked(?string $status = null): bool
    {
        return $status === self::Blocked->value;
    }

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
