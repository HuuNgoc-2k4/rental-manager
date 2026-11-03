<?php

namespace App\Repositories;

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
     * @return Collection
     */
    public function getOverview(): Collection
    {
        return $this->newQuery()
            ->with(['activeContract.residents.tenant', 'activeContract.document'])
            // Độ dài trước để 201 đứng trước 1001.
            ->orderByRaw('LENGTH(name)')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param Room $room
     * @return Room
     */
    public function loadDetail(Room $room): Room
    {
        return $room->load(['property', 'currentMeters', 'activeContract.liveLinks.tenant', 'activeContract.document']);
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
}
