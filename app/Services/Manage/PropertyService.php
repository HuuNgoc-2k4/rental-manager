<?php

namespace App\Services\Manage;

use App\Exceptions\BusinessException;
use App\Models\Property;
use App\Repositories\Interfaces\PropertyRepositoryInterface;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyService extends BaseService
{
    protected PropertyRepositoryInterface $propertyRepository;

    /**
     * @param Property $model
     * @param PropertyRepositoryInterface $propertyRepository
     */
    public function __construct(Property $model, PropertyRepositoryInterface $propertyRepository)
    {
        parent::__construct($model);
        $this->propertyRepository = $propertyRepository;
    }

    /**
     * @return Collection
     */
    public function getList(): Collection
    {
        return $this->propertyRepository->listWithRoomCounts();
    }

    /**
     * @param array $data
     * @return array{status: bool, message: string}
     */
    public function store(array $data): array
    {
        try {
            DB::beginTransaction();
            $this->propertyRepository->create($this->payload($data));
            DB::commit();

            return ['status' => true, 'message' => 'Đã thêm khu trọ.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create property error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi thêm khu trọ.'];
        }
    }

    /**
     * @param Property $property
     * @param array $data
     * @return array{status: bool, message: string}
     */
    public function update(Property $property, array $data): array
    {
        try {
            DB::beginTransaction();
            $this->propertyRepository->update($property, $this->payload($data));
            DB::commit();

            return ['status' => true, 'message' => 'Đã cập nhật khu trọ.'];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update property error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi cập nhật khu trọ.'];
        }
    }

    /**
     * Chỉ xóa được khu chưa có phòng.
     *
     * @param Property $property
     * @return array{status: bool, message: string}
     */
    public function delete(Property $property): array
    {
        try {
            DB::beginTransaction();

            if ($this->propertyRepository->hasRooms($property)) {
                throw new BusinessException('Khu này còn phòng, hãy xóa hoặc chuyển hết phòng trước.', 'property_has_rooms');
            }

            $this->propertyRepository->delete($property);
            DB::commit();

            return ['status' => true, 'message' => 'Đã xóa khu trọ.'];
        } catch (BusinessException $e) {
            DB::rollBack();

            return ['status' => false, 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delete property error: '.$e->getMessage());

            return ['status' => false, 'message' => 'Có lỗi xảy ra khi xóa khu trọ.'];
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
            'address' => trim($data['address']),
            'note' => $data['note'] ?? null
        ];
    }
}
