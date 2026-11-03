<?php

namespace App\Enums\Property;

/**
 * Hiện trạng một món nội thất trong checklist phòng.
 */
enum FurnitureCondition: string
{
    case Good = 'good';
    case Worn = 'worn';
    case Broken = 'broken';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Good => 'Tốt',
            self::Worn => 'Cũ, dùng được',
            self::Broken => 'Hỏng'
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
            self::Good => 'bg-green-50 text-green-700 ring-green-600/20',
            self::Worn => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::Broken => 'bg-rose-50 text-rose-700 ring-rose-600/20'
        };
    }

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
