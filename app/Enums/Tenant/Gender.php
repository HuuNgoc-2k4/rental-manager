<?php

namespace App\Enums\Tenant;

/**
 * Giới tính.
 */
enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Male => 'Nam',
            self::Female => 'Nữ',
            self::Other => 'Khác'
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
            self::Male => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
            self::Female => 'bg-rose-50 text-rose-700 ring-rose-600/20',
            self::Other => 'bg-slate-50 text-slate-700 ring-slate-600/20'
        };
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isMale(?string $value = null): bool
    {
        return $value === self::Male->value;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isFemale(?string $value = null): bool
    {
        return $value === self::Female->value;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isOther(?string $value = null): bool
    {
        return $value === self::Other->value;
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
