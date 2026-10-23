<?php

namespace App\Enums\Property;

/**
 * Trạng thái phòng. Occupied chỉ do service hợp đồng bật / tắt.
 */
enum RoomStatus: string
{
    case Vacant = 'vacant';
    case Occupied = 'occupied';
    case Maintenance = 'maintenance';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Vacant => 'Trống',
            self::Occupied => 'Đang thuê',
            self::Maintenance => 'Đang sửa'
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
            self::Vacant => 'bg-green-50 text-green-700 ring-green-600/20',
            self::Occupied => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
            self::Maintenance => 'bg-amber-50 text-amber-700 ring-amber-600/20'
        };
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isVacant(?string $value = null): bool
    {
        return $value === self::Vacant->value;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isOccupied(?string $value = null): bool
    {
        return $value === self::Occupied->value;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isMaintenance(?string $value = null): bool
    {
        return $value === self::Maintenance->value;
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
