<?php

namespace App\Repositories\Interfaces;

use App\Models\Property;
use Illuminate\Database\Eloquent\Collection;

interface PropertyRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Danh sách khu kèm số phòng theo trạng thái.
     *
     * @return Collection
     */
    public function listWithRoomCounts(): Collection;

    /**
     * @param Property $property
     * @return bool
     */
    public function hasRooms(Property $property): bool;
}
