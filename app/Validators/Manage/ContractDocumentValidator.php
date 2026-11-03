<?php

namespace App\Validators\Manage;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ContractDocumentValidator
{
    /** Ngày thanh toán tối đa: 28 để tháng nào cũng có ngày đó. */
    private const MAX_PAYMENT_DAY = 28;

    /**
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function updateValidator(array $input)
    {
        $party = fn (string $key) => [
            "{$key}" => ['required', 'array'],
            "{$key}.full_name" => ['required', 'string', 'max:255'],
            "{$key}.date_of_birth" => ['nullable', 'date', 'before:today'],
            "{$key}.id_number" => ['nullable', 'string', 'max:20'],
            "{$key}.id_issued_date" => ['nullable', 'date', 'before_or_equal:today'],
            "{$key}.id_issued_place" => ['nullable', 'string', 'max:255'],
            "{$key}.permanent_address" => ['nullable', 'string', 'max:500'],
            "{$key}.phone" => ['nullable', 'regex:'.User::PHONE_REGEX]
        ];

        return Validator::make($input, $party('landlord') + $party('tenant') + [
            'code' => ['required', 'string', 'max:50'],
            'signed_date' => ['required', 'date'],
            'signed_place' => ['nullable', 'string', 'max:500'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'members' => ['nullable', 'array'],
            'members.*.full_name' => ['required', 'string', 'max:255'],
            'members.*.date_of_birth' => ['nullable', 'date', 'before:today'],
            'members.*.id_number' => ['nullable', 'string', 'max:20'],
            'members.*.phone' => ['nullable', 'regex:'.User::PHONE_REGEX],
            'property_address' => ['required', 'string', 'max:500'],
            'room_name' => ['required', 'string', 'max:50'],
            'room_area' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'max_occupants' => ['required', 'integer', 'min:1', 'max:50'],
            'equipment' => ['nullable', 'string', 'max:2000'],
            'rent' => ['required', 'integer', 'min:0'],
            'deposit' => ['required', 'integer', 'min:0'],
            'electric_price' => ['required', 'integer', 'min:0'],
            'water_price' => ['required', 'integer', 'min:0'],
            'services' => ['nullable', 'array'],
            'services.*.name' => ['required', 'string', 'max:100'],
            'services.*.amount' => ['required', 'integer', 'min:0'],
            'services.*.unit' => ['nullable', 'string', 'max:50'],
            'payment_day' => ['required', 'integer', 'min:1', 'max:'.self::MAX_PAYMENT_DAY],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'extra_terms' => ['nullable', 'string', 'max:10000']
        ], [
            'landlord.full_name.required' => 'Vui lòng nhập họ tên Bên A (bên cho thuê).',
            'tenant.full_name.required' => 'Vui lòng nhập họ tên Bên B (bên thuê).',
            '*.date_of_birth.before' => 'Ngày sinh phải trước hôm nay.',
            '*.phone.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng 0.',
            'members.*.full_name.required' => 'Vui lòng nhập họ tên người ở cùng (hoặc xóa dòng trống).',
            'members.*.phone.regex' => 'Số điện thoại người ở cùng phải gồm 10 số và bắt đầu bằng 0.',
            'code.required' => 'Vui lòng nhập số hợp đồng.',
            'signed_date.required' => 'Vui lòng chọn ngày lập hợp đồng.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu thuê.',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
            'property_address.required' => 'Vui lòng nhập địa chỉ phòng thuê.',
            'room_name.required' => 'Vui lòng nhập số phòng.',
            'max_occupants.min' => 'Số người tối đa phải từ 1.',
            'rent.required' => 'Vui lòng nhập giá thuê.',
            'deposit.required' => 'Vui lòng nhập tiền cọc.',
            'electric_price.required' => 'Vui lòng nhập đơn giá điện.',
            'water_price.required' => 'Vui lòng nhập đơn giá nước.',
            'services.*.name.required' => 'Vui lòng nhập tên khoản phí (hoặc xóa dòng trống).',
            'services.*.amount.required' => 'Vui lòng nhập số tiền của khoản phí.',
            'payment_day.max' => 'Ngày thanh toán từ 1 đến '.self::MAX_PAYMENT_DAY.' để tháng nào cũng có.',
            '*.integer' => 'Số tiền phải là số nguyên.',
            '*.min' => 'Giá trị không được âm.'
        ]);
    }
}
