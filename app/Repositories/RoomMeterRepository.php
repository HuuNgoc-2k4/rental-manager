<?php

namespace App\Repositories;

use App\Models\Room;
use App\Models\RoomMeter;
use App\Repositories\Interfaces\RoomMeterRepositoryInterface;

class RoomMeterRepository extends BaseRepository implements RoomMeterRepositoryInterface
{
    /**
     * @param RoomMeter $model
     */
    public function __construct(RoomMeter $model)
    {
        parent::__construct($model);
    }

    /**
     * @param int $roomId
     * @param string $type
     * @return RoomMeter|null
     */
    public function findCurrent(int $roomId, string $type): ?RoomMeter
    {
        return $this->newQuery()
            ->where('room_id', $roomId)
            ->where('type', $type)
            ->whereNull('replaced_at')
            ->first();
    }

    /**
     * @param Room $room
     * @return int
     */
    public function deleteByRoom(Room $room): int
    {
        return $this->newQuery()->where('room_id', $room->id)->delete();
    }
}
