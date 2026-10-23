<?php

namespace App\Enums\Meter;

/**
 * Loại đồng hồ trong phòng.
 */
enum MeterType: string
{
    case Electric = 'electric';
    case Water = 'water';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Electric => 'Điện',
            self::Water => 'Nước'
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
            self::Electric => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::Water => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20'
        };
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isElectric(?string $value = null): bool
    {
        return $value === self::Electric->value;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isWater(?string $value = null): bool
    {
        return $value === self::Water->value;
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
