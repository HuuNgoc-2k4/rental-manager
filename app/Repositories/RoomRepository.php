<?php

namespace App\Repositories;

use App\Models\Property;
use App\Models\Room;
use App\Repositories\Interfaces\RoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RoomRepository extends BaseRepository implements RoomRepositoryInterface
{
    /**
     * @param Room $model
     */
    public function __construct(Room $model)
    {
        parent::__construct($model);
    }

    /**
     * @param Property $property
     * @return Collection
     */
    public function getForMap(Property $property): Collection
    {
        return $this->newQuery()
            ->where('property_id', $property->id)
            ->with(['currentMeters', 'activeContract.residents.tenant'])
            ->orderBy('floor')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param Room $room
     * @return bool
     */
    public function hasContracts(Room $room): bool
    {
        return $room->contracts()->exists();
    }

    /**
     * @param Room $room
     * @return bool
     */
    public function hasActiveContract(Room $room): bool
    {
        return $room->activeContract()->exists();
    }

    /**
     * @param Room $room
     * @return int
     */
    public function countResidents(Room $room): int
    {
        return (int) $room->activeContract()->first()?->residents()->count();
    }
}
