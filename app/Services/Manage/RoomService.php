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
     * @return Collection
     */
    public function getOverview(): Collection
    {
        return $this->roomRepository->getOverview();
    }

    /**
     * @param Room $room
     * @return Room
     */
    public function getDetail(Room $room): Room
    {
        return $this->roomRepository->loadDetail($room);
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
     * @param Room $room
     * @param array $data
     * @return array{status: bool, message: string}
     */
    public function update(Room $room, array $data): array
    {
        try {
            DB::beginTransaction();

            $this->roomRepository->update($room, $this->payload($data));

            foreach (MeterType::values() as $type) {
                if (isset($data["{$type}_integer_digits"])) {
                    $this->syncCurrentMeter($room, $type, $data);
                }
            }

            DB::commit();

            return ['status' => true, 'message' => 'Đã cập nhật phòng.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update room error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi cập nhật phòng.'];
        }
    }

    /**
     * Lưu cả checklist nội thất một lần (thay toàn bộ danh sách cũ).
     *
     * @param Room $room
     * @param array $items [{name, quantity, condition, note}]
     * @return array{status: bool, message: string}
     */
    public function updateFurniture(Room $room, array $items): array
    {
        try {
            DB::beginTransaction();

            $this->roomRepository->update($room, [
                'furniture' => array_map(fn (array $item) => [
                    'name' => trim($item['name']),
                    'quantity' => (int) $item['quantity'],
                    'condition' => $item['condition'],
                    'note' => filled($item['note'] ?? null) ? trim($item['note']) : null
                ], array_values($items))
            ]);
            DB::commit();

            return ['status' => true, 'message' => 'Đã lưu nội thất và hiện trạng phòng.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update room furniture error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi lưu nội thất.'];
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
     * Sửa tại chỗ đồng hồ đang dùng (đổi số chữ số / chỉ số đầu); phòng chưa có thì tạo mới.
     *
     * @param Room $room
     * @param string $type
     * @param array $data
     * @return void
     */
    private function syncCurrentMeter(Room $room, string $type, array $data): void
    {
        $values = [
            'integer_digits' => $data["{$type}_integer_digits"],
            'decimal_digits' => $data["{$type}_decimal_digits"],
            'initial_reading' => $data["{$type}_initial_reading"]
        ];
        $current = $this->meterRepository->findCurrent($room->id, $type);

        if ($current) {
            $this->meterRepository->update($current, $values);
        } else {
            $this->meterRepository->create($values + ['room_id' => $room->id, 'type' => $type, 'installed_at' => now()->toDateString()]);
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
            'room_type' => $data['room_type'] ?? null,
            'area' => $data['area'] ?? null,
            'base_rent' => $data['base_rent'],
            'note' => $data['note'] ?? null
        ];
    }
}
