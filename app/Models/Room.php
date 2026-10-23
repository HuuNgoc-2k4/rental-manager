<?php

namespace App\Models;

use App\Enums\Contract\ContractStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['property_id', 'name', 'floor', 'area', 'max_occupants', 'base_rent', 'status', 'note'])]
class Room extends Model
{
    /**
     * @return BelongsTo
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * @return HasMany
     */
    public function meters(): HasMany
    {
        return $this->hasMany(RoomMeter::class);
    }

    /**
     * @return HasMany
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    /**
     * Hợp đồng đang hiệu lực (tối đa 1 theo bất biến của service).
     *
     * @return HasOne
     */
    public function activeContract(): HasOne
    {
        return $this->hasOne(Contract::class)->where('status', ContractStatus::Active->value);
    }

    /**
     * Đồng hồ đang dùng (chưa thay).
     *
     * @return HasMany
     */
    public function currentMeters(): HasMany
    {
        return $this->hasMany(RoomMeter::class)->whereNull('replaced_at');
    }
}
