<?php

namespace App\Validators\Manage;

use App\Enums\Meter\MeterType;
use App\Enums\Property\RoomStatus;
use App\Models\Property;
use App\Models\Room;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class RoomValidator
{
    /**
     * @param array $input
     * @param Property $property
     * @return \Illuminate\Validation\Validator
     */
    public function storeValidator(array $input, Property $property)
    {
        $rules = $this->roomRules(Rule::unique('rooms', 'name')->where('property_id', $property->id));

        foreach (MeterType::values() as $type) {
            $rules["{$type}_integer_digits"] = ['required', 'integer', 'between:1,10'];
            $rules["{$type}_decimal_digits"] = ['required', 'integer', 'between:0,3'];
            $rules["{$type}_initial_reading"] = ['required', 'numeric', 'min:0', 'max:9999999999'];
        }

        $validator = Validator::make($input, $rules, $this->messages());

        // Chỉ số đầu phải nằm trong số chữ số đã khai báo (VD 5 chữ số → tối đa 99999).
        $validator->after(function ($v) use ($input) {
            foreach (MeterType::cases() as $type) {
                $this->checkReadingFitsDigits($v, $input, $type->value, $type->label());
            }
        });

        return $validator;
    }

    /**
     * @param array $input
     * @param Room $room
     * @return \Illuminate\Validation\Validator
     */
    public function updateValidator(array $input, Room $room)
    {
        return Validator::make(
            $input,
            $this->roomRules(Rule::unique('rooms', 'name')->where('property_id', $room->property_id)->ignore($room->id)),
            $this->messages()
        );
    }

    /**
     * Chủ nhà chỉ đặt tay được Vacant <-> Maintenance; Occupied do service hợp đồng quyết định.
     *
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function statusValidator(array $input)
    {
        return Validator::make($input, [
            'status' => ['required', Rule::in([RoomStatus::Vacant->value, RoomStatus::Maintenance->value])]
        ], [
            'status.required' => 'Vui lòng chọn trạng thái phòng.',
            'status.in' => 'Chỉ được đặt phòng ở trạng thái Trống hoặc Đang sửa.'
        ]);
    }

    /**
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function replaceMeterValidator(array $input)
    {
        $validator = Validator::make($input, [
            'type' => ['required', Rule::in(MeterType::values())],
            'integer_digits' => ['required', 'integer', 'between:1,10'],
            'decimal_digits' => ['required', 'integer', 'between:0,3'],
            'initial_reading' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'replaced_at' => ['required', 'date', 'before_or_equal:today']
        ], [
            'type.required' => 'Vui lòng chọn loại đồng hồ.',
            'type.in' => 'Loại đồng hồ không hợp lệ.',
            'integer_digits.required' => 'Vui lòng nhập số chữ số phần nguyên.',
            'integer_digits.between' => 'Số chữ số phần nguyên từ 1 đến 10.',
            'decimal_digits.required' => 'Vui lòng nhập số chữ số thập phân.',
            'decimal_digits.between' => 'Số chữ số thập phân từ 0 đến 3.',
            'initial_reading.required' => 'Vui lòng nhập chỉ số đầu của đồng hồ mới.',
            'initial_reading.min' => 'Chỉ số đầu không được âm.',
            'replaced_at.required' => 'Vui lòng chọn ngày thay đồng hồ.',
            'replaced_at.before_or_equal' => 'Ngày thay không được ở tương lai.'
        ]);

        $validator->after(function ($v) use ($input) {
            $this->checkReadingFitsDigits($v, $input, null, 'Đồng hồ');
        });

        return $validator;
    }

    /**
     * @param \Illuminate\Validation\Validator $validator
     * @param array $input
     * @param string|null $prefix Tiền tố field (electric / water) hoặc null khi field không có tiền tố
     * @param string $label
     * @return void
     */
    private function checkReadingFitsDigits($validator, array $input, ?string $prefix, string $label): void
    {
        $key = fn (string $field) => $prefix ? "{$prefix}_{$field}" : $field;

        if ($validator->errors()->hasAny([$key('integer_digits'), $key('decimal_digits'), $key('initial_reading')])) {
            return;
        }

        $limit = 10 ** (int) $input[$key('integer_digits')];

        if ((float) $input[$key('initial_reading')] >= $limit) {
            $validator->errors()->add($key('initial_reading'), "Chỉ số đầu của đồng hồ {$label} vượt quá số chữ số đã khai báo.");
        }
    }

    /**
     * @param Unique $uniqueName
     * @return array
     */
    private function roomRules($uniqueName): array
    {
        return [
            'name' => ['required', 'string', 'max:50', $uniqueName],
            'floor' => ['nullable', 'integer', 'between:-5,100'],
            'area' => ['nullable', 'numeric', 'min:1', 'max:9999'],
            'max_occupants' => ['required', 'integer', 'between:1,20'],
            'base_rent' => ['required', 'integer', 'min:0', 'max:100000000000'],
            'note' => ['nullable', 'string', 'max:2000']
        ];
    }

    /**
     * @return array
     */
    private function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên / số phòng.',
            'name.max' => 'Tên phòng tối đa 50 ký tự.',
            'name.unique' => 'Khu này đã có phòng trùng tên.',
            'floor.integer' => 'Tầng phải là số nguyên.',
            'floor.between' => 'Tầng không hợp lệ.',
            'area.numeric' => 'Diện tích phải là số.',
            'area.min' => 'Diện tích tối thiểu 1 m².',
            'max_occupants.required' => 'Vui lòng nhập số người tối đa.',
            'max_occupants.between' => 'Số người tối đa từ 1 đến 20.',
            'base_rent.required' => 'Vui lòng nhập giá thuê.',
            'base_rent.integer' => 'Giá thuê phải là số nguyên (VND).',
            'base_rent.min' => 'Giá thuê không được âm.',
            'electric_integer_digits.required' => 'Vui lòng nhập số chữ số phần nguyên của đồng hồ điện.',
            'water_integer_digits.required' => 'Vui lòng nhập số chữ số phần nguyên của đồng hồ nước.',
            'electric_integer_digits.between' => 'Đồng hồ điện: số chữ số phần nguyên từ 1 đến 10.',
            'water_integer_digits.between' => 'Đồng hồ nước: số chữ số phần nguyên từ 1 đến 10.',
            'electric_decimal_digits.required' => 'Vui lòng nhập số chữ số thập phân của đồng hồ điện.',
            'water_decimal_digits.required' => 'Vui lòng nhập số chữ số thập phân của đồng hồ nước.',
            'electric_decimal_digits.between' => 'Đồng hồ điện: số chữ số thập phân từ 0 đến 3.',
            'water_decimal_digits.between' => 'Đồng hồ nước: số chữ số thập phân từ 0 đến 3.',
            'electric_initial_reading.required' => 'Vui lòng nhập chỉ số đầu của đồng hồ điện.',
            'water_initial_reading.required' => 'Vui lòng nhập chỉ số đầu của đồng hồ nước.',
            'electric_initial_reading.min' => 'Chỉ số đầu đồng hồ điện không được âm.',
            'water_initial_reading.min' => 'Chỉ số đầu đồng hồ nước không được âm.'
        ];
    }
}
