<?php

namespace App\Enums\Tenant;

/**
 * Trạng thái khai báo thông tin cá nhân.
 */
enum TenantInfoStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Verified = 'verified';
    case Rejected = 'rejected';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Chưa khai báo',
            self::Submitted => 'Chờ duyệt',
            self::Verified => 'Đã xác minh',
            self::Rejected => 'Bị trả lại'
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
            self::Draft => 'bg-slate-50 text-slate-700 ring-slate-600/20',
            self::Submitted => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::Verified => 'bg-green-50 text-green-700 ring-green-600/20',
            self::Rejected => 'bg-rose-50 text-rose-700 ring-rose-600/20'
        };
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isDraft(?string $value = null): bool
    {
        return $value === self::Draft->value;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isSubmitted(?string $value = null): bool
    {
        return $value === self::Submitted->value;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isVerified(?string $value = null): bool
    {
        return $value === self::Verified->value;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isRejected(?string $value = null): bool
    {
        return $value === self::Rejected->value;
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
