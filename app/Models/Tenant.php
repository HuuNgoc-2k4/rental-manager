<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'full_name', 'phone', 'email', 'date_of_birth', 'gender', 'id_number', 'id_issued_date', 'id_issued_place', 'permanent_address', 'occupation', 'id_front_path', 'id_back_path', 'info_status', 'info_reject_reason', 'info_verified_at', 'info_verified_by', 'note'])]
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

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
}
