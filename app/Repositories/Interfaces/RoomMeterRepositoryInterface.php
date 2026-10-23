<?php

namespace App\Repositories\Interfaces;

use App\Models\Room;
use App\Models\RoomMeter;

interface RoomMeterRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Đồng hồ đang dùng của phòng theo loại.
     *
     * @param int $roomId
     * @param string $type
     * @return RoomMeter|null
     */
    public function findCurrent(int $roomId, string $type): ?RoomMeter;

    /**
     * @param Room $room
     * @return int
     */
    public function deleteByRoom(Room $room): int;
}
