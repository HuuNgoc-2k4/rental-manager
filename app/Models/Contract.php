<?php

namespace App\Models;

use App\Enums\Contract\MemberApproval;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['room_id', 'start_date', 'end_date', 'ended_at', 'status', 'note'])]
class Contract extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'ended_at' => 'date'];
    }

    /**
     * @return BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * @return HasMany
     */
    public function contractTenants(): HasMany
    {
        return $this->hasMany(ContractTenant::class);
    }

    /**
     * Người đang ở: đã duyệt và chưa rời đi.
     *
     * @return HasMany
     */
    public function residents(): HasMany
    {
        return $this->hasMany(ContractTenant::class)
            ->whereNull('left_at')
            ->where('approval_status', MemberApproval::Approved->value);
    }

    /**
     * Người đang nằm trong hộ (chưa rời), kể cả chờ duyệt.
     *
     * @return HasMany
     */
    public function liveLinks(): HasMany
    {
        return $this->hasMany(ContractTenant::class)->whereNull('left_at')->orderByDesc('is_primary')->orderBy('id');
    }
}
