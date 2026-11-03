<?php

namespace Database\Seeders;

use App\Enums\Meter\MeterType;
use App\Enums\Property\RoomStatus;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomMeter;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /** Số phòng mỗi tầng của mỗi khu mẫu. */
    private const ROOMS_PER_FLOOR = 3;

    /**
     * 2 khu mẫu, mỗi khu 2 tầng x 3 phòng = 12 phòng, mỗi phòng có đồng hồ điện + nước.
     */
    public function run(): void
    {
        $properties = [
            ['name' => 'Toà A', 'address' => 'Ngõ 12 Nguyễn Trãi, Thanh Xuân, Hà Nội', 'prefix' => ''],
            ['name' => 'Toà B', 'address' => 'Số 5 Lê Văn Lương, Thanh Xuân, Hà Nội', 'prefix' => 'B']
        ];

        foreach ($properties as $item) {
            $property = Property::query()->firstOrCreate(['name' => $item['name']], ['address' => $item['address']]);

            for ($floor = 1; $floor <= 2; $floor++) {
                for ($index = 1; $index <= self::ROOMS_PER_FLOOR; $index++) {
                    $room = Room::query()->firstOrCreate(
                        ['property_id' => $property->id, 'name' => $item['prefix'].($floor * 100 + $index)],
                        [
                            'area' => 22 + $index * 3,
                            'base_rent' => 2500000 + $floor * 200000,
                            'status' => RoomStatus::Vacant->value
                        ]
                    );

                    $this->seedMeters($room);
                }
            }
        }
    }

    /**
     * @param Room $room
     * @return void
     */
    private function seedMeters(Room $room): void
    {
        if (RoomMeter::query()->where('room_id', $room->id)->exists()) {
            return;
        }

        foreach ([MeterType::Electric->value => 0, MeterType::Water->value => 1] as $type => $decimalDigits) {
            RoomMeter::query()->create([
                'room_id' => $room->id,
                'type' => $type,
                'integer_digits' => 5,
                'decimal_digits' => $decimalDigits,
                'initial_reading' => 0,
                'installed_at' => now()->subYear()->toDateString()
            ]);
        }
    }
}
