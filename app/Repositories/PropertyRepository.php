<?php

namespace App\Repositories;

use App\Enums\Property\RoomStatus;
use App\Models\Property;
use App\Repositories\Interfaces\PropertyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PropertyRepository extends BaseRepository implements PropertyRepositoryInterface
{
    /**
     * @param Property $model
     */
    public function __construct(Property $model)
    {
        parent::__construct($model);
    }

    /**
     * @return Collection
     */
    public function listWithRoomCounts(): Collection
    {
        return $this->newQuery()
            ->withCount([
                'rooms',
                'rooms as vacant_rooms_count' => fn ($q) => $q->where('status', RoomStatus::Vacant->value),
                'rooms as occupied_rooms_count' => fn ($q) => $q->where('status', RoomStatus::Occupied->value),
                'rooms as maintenance_rooms_count' => fn ($q) => $q->where('status', RoomStatus::Maintenance->value)
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * @param Property $property
     * @return bool
     */
    public function hasRooms(Property $property): bool
    {
        return $property->rooms()->exists();
    }
}
