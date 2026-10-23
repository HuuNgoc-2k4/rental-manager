<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['room_id', 'type', 'integer_digits', 'decimal_digits', 'initial_reading', 'installed_at', 'replaced_at'])]
class RoomMeter extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['installed_at' => 'date', 'replaced_at' => 'date'];
    }

    /**
     * @return BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
