<?php

namespace App\Models;

use App\Enums\Tenant\TenantInfoStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'full_name', 'phone', 'email', 'date_of_birth', 'gender', 'id_number', 'id_issued_date', 'id_issued_place', 'permanent_address', 'occupation', 'id_front_path', 'id_back_path', 'info_status', 'info_reject_reason', 'info_verified_at', 'info_verified_by', 'note'])]
class Tenant extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['date_of_birth' => 'date', 'id_issued_date' => 'date', 'info_verified_at' => 'datetime'];
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany
     */
    public function contractTenants(): HasMany
    {
        return $this->hasMany(ContractTenant::class);
    }

    /**
     * Che CCCD cho danh sách (0012****5678); drawer chi tiết mới hiện đủ.
     *
     * @return string|null
     */
    public function maskedIdNumber(): ?string
    {
        if (!$this->id_number) {
            return null;
        }

        return strlen($this->id_number) <= 6
            ? str_repeat('*', strlen($this->id_number))
            : substr($this->id_number, 0, 4).str_repeat('*', strlen($this->id_number) - 8).substr($this->id_number, -4);
    }

    /**
     * @return bool
     */
    public function isVerified(): bool
    {
        return TenantInfoStatus::isVerified($this->info_status);
    }
}
