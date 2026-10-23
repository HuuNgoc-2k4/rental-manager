<?php

namespace App\Enums\Contract;

/**
 * Trạng thái hợp đồng (hộ thuê).
 */
enum ContractStatus: string
{
    case Active = 'active';
    case Ended = 'ended';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Đang hiệu lực',
            self::Ended => 'Đã kết thúc'
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
            self::Active => 'bg-green-50 text-green-700 ring-green-600/20',
            self::Ended => 'bg-slate-50 text-slate-700 ring-slate-600/20'
        };
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isActive(?string $value = null): bool
    {
        return $value === self::Active->value;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isEnded(?string $value = null): bool
    {
        return $value === self::Ended->value;
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
