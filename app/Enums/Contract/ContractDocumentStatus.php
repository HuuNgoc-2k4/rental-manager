<?php

namespace App\Enums\Contract;

/**
 * Trạng thái văn bản hợp đồng: admin lập (nháp) → gửi chủ hộ (chờ duyệt) → chủ hộ duyệt / yêu cầu sửa.
 */
enum ContractDocumentStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * @return string
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Bản nháp',
            self::Pending => 'Chờ chủ hộ duyệt',
            self::Approved => 'Đã duyệt',
            self::Rejected => 'Yêu cầu sửa'
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
            self::Pending => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            self::Approved => 'bg-green-50 text-green-700 ring-green-600/20',
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
    public static function isPending(?string $value = null): bool
    {
        return $value === self::Pending->value;
    }

    /**
     * @param string|null $value
     * @return bool
     */
    public static function isApproved(?string $value = null): bool
    {
        return $value === self::Approved->value;
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
