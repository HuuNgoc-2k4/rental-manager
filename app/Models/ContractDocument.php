<?php

namespace App\Models;

use App\Enums\Contract\ContractDocumentStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NumberFormatter;

#[Fillable([
    'contract_id', 'code', 'status', 'signed_date', 'signed_place', 'start_date', 'end_date', 'landlord', 'tenant', 'members',
    'property_address', 'room_name', 'room_area', 'max_occupants', 'equipment', 'equipment_items', 'rent', 'deposit', 'electric_price', 'water_price',
    'services', 'payment_day', 'payment_method', 'extra_terms', 'reject_reason', 'submitted_at', 'approved_at', 'approved_by', 'created_by'
])]
class ContractDocument extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'signed_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'landlord' => 'array',
            'tenant' => 'array',
            'members' => 'array',
            'services' => 'array',
            'equipment_items' => 'array',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime'
        ];
    }

    /**
     * @return BelongsTo
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Đã duyệt thì khóa: không sửa nữa, chỉ in.
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return ContractDocumentStatus::isApproved($this->status);
    }

    /**
     * Số tiền bằng chữ cho văn bản: 2500000 → "Hai triệu năm trăm nghìn đồng".
     *
     * @param int $amount
     * @return string
     */
    public static function amountInWords(int $amount): string
    {
        $words = (new NumberFormatter('vi', NumberFormatter::SPELLOUT))->format($amount);

        return mb_strtoupper(mb_substr($words, 0, 1)).mb_substr($words, 1).' đồng';
    }
}
