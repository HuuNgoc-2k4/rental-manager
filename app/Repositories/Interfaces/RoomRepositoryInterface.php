<?php

namespace App\Repositories\Interfaces;

use App\Models\Property;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

interface RoomRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Phòng của một khu kèm đồng hồ đang dùng và hộ đang ở (cho sơ đồ phòng).
     *
     * @param Property $property
     * @return Collection
     */
    public function getForMap(Property $property): Collection;

    /**
     * @param Room $room
     * @return bool
     */
    public function hasContracts(Room $room): bool;

    /**
     * @param Room $room
     * @return bool
     */
    public function hasActiveContract(Room $room): bool;

    /**
     * Số người đang ở (đã duyệt, chưa rời) của hợp đồng hiệu lực.
     *
     * @param Room $room
     * @return int
     */
    public function countResidents(Room $room): int;
}
