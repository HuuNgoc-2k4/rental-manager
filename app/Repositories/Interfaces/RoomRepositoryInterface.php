<?php

namespace App\Repositories\Interfaces;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

interface RoomRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Tất cả phòng kèm hộ đang ở và văn bản hợp đồng (cho danh sách phòng).
     *
     * @return Collection
     */
    public function getOverview(): Collection;

    /**
     * Nạp đủ dữ liệu cho trang chi tiết phòng.
     *
     * @param Room $room
     * @return Room
     */
    public function loadDetail(Room $room): Room;

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
}
