<?php

namespace App\Services\Manage;

use App\Enums\Meter\MeterType;
use App\Enums\Property\RoomStatus;
use App\Exceptions\BusinessException;
use App\Models\Property;
use App\Models\Room;
use App\Repositories\Interfaces\RoomMeterRepositoryInterface;
use App\Repositories\Interfaces\RoomRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomService extends BaseService
{
    protected RoomRepositoryInterface $roomRepository;

    protected RoomMeterRepositoryInterface $meterRepository;

    /**
     * @param Room $model
     * @param RoomRepositoryInterface $roomRepository
     * @param RoomMeterRepositoryInterface $meterRepository
     */
    public function __construct(Room $model, RoomRepositoryInterface $roomRepository, RoomMeterRepositoryInterface $meterRepository)
    {
        parent::__construct($model);
        $this->roomRepository = $roomRepository;
        $this->meterRepository = $meterRepository;
    }

    /**
     * @param Property $property
     * @return Collection
     */
    public function getForMap(Property $property): Collection
    {
        return $this->roomRepository->getForMap($property);
    }

    /**
     * Tạo phòng kèm 2 đồng hồ điện / nước (khai báo số chữ số ngay khi setup phòng).
     *
     * @param Property $property
     * @param array $data
     * @return array{status: bool, message: string}
     */
    public function store(Property $property, array $data): array
    {
        try {
            DB::beginTransaction();

            $room = $this->roomRepository->create($this->payload($data) + [
                'property_id' => $property->id,
                'status' => RoomStatus::Vacant->value
            ]);

            foreach (MeterType::values() as $type) {
                $this->meterRepository->create([
                    'room_id' => $room->id,
                    'type' => $type,
                    'integer_digits' => $data["{$type}_integer_digits"],
                    'decimal_digits' => $data["{$type}_decimal_digits"],
                    'initial_reading' => $data["{$type}_initial_reading"],
                    'installed_at' => now()->toDateString()
                ]);
            }

            DB::commit();

            return ['status' => true, 'message' => "Đã thêm phòng {$room->name}."];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create room error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi thêm phòng.'];
        }
    }

    /**
     * Không cho hạ số người tối đa xuống thấp hơn số người đang ở.
     *
     * @param Room $room
     * @param array $data
     * @return array{status: bool, message: string}
     */
    public function update(Room $room, array $data): array
    {
        try {
            DB::beginTransaction();

            if ((int) $data['max_occupants'] < $this->roomRepository->countResidents($room)) {
                throw new BusinessException('Số người tối đa không được thấp hơn số người đang ở.', 'max_occupants_too_low');
            }

            $this->roomRepository->update($room, $this->payload($data));
            DB::commit();

            return ['status' => true, 'message' => 'Đã cập nhật phòng.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update room error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi cập nhật phòng.'];
        }
    }

    /**
     * Đặt tay Trống <-> Đang sửa, chỉ khi phòng không có hợp đồng hiệu lực.
     *
     * @param Room $room
     * @param string $status
     * @return array{status: bool, message: string}
     */
    public function updateStatus(Room $room, string $status): array
    {
        try {
            DB::beginTransaction();

            if ($this->roomRepository->hasActiveContract($room)) {
                throw new BusinessException('Phòng đang có hợp đồng, không thể đổi trạng thái thủ công.', 'room_occupied');
            }

            $this->roomRepository->update($room, ['status' => $status]);
            DB::commit();

            return ['status' => true, 'message' => 'Phòng '.$room->name.' đã chuyển sang: '.RoomStatus::from($status)->label().'.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update room status error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi đổi trạng thái phòng.'];
        }
    }

    /**
     * Thay đồng hồ: đóng đồng hồ cũ (replaced_at) và mở đồng hồ mới để chỉ số quay về 0 không phá luật chốt số.
     *
     * @param Room $room
     * @param array $data
     * @return array{status: bool, message: string}
     */
    public function replaceMeter(Room $room, array $data): array
    {
        try {
            DB::beginTransaction();

            $current = $this->meterRepository->findCurrent($room->id, $data['type']);
            if ($current) {
                $this->meterRepository->update($current, ['replaced_at' => $data['replaced_at']]);
            }

            $this->meterRepository->create([
                'room_id' => $room->id,
                'type' => $data['type'],
                'integer_digits' => $data['integer_digits'],
                'decimal_digits' => $data['decimal_digits'],
                'initial_reading' => $data['initial_reading'],
                'installed_at' => $data['replaced_at']
            ]);

            DB::commit();

            return ['status' => true, 'message' => 'Đã thay đồng hồ '.MeterType::from($data['type'])->label().' của phòng '.$room->name.'.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Replace meter error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi thay đồng hồ.'];
        }
    }

    /**
     * Chỉ xóa được phòng chưa từng có hợp đồng (kể cả đã kết thúc).
     *
     * @param Room $room
     * @return array{status: bool, message: string}
     */
    public function delete(Room $room): array
    {
        try {
            DB::beginTransaction();

            if ($this->roomRepository->hasContracts($room)) {
                throw new BusinessException('Phòng đã có lịch sử hợp đồng nên không thể xóa.', 'room_has_contracts');
            }

            $this->meterRepository->deleteByRoom($room);
            $this->roomRepository->delete($room);
            DB::commit();

            return ['status' => true, 'message' => 'Đã xóa phòng.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete room error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi xóa phòng.'];
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function payload(array $data): array
    {
        return [
            'name' => trim($data['name']),
            'floor' => $data['floor'] ?? null,
            'area' => $data['area'] ?? null,
            'max_occupants' => $data['max_occupants'],
            'base_rent' => $data['base_rent'],
            'note' => $data['note'] ?? null
        ];
    }
}
