<?php

namespace App\Validators\Manage;

use App\Enums\Tenant\Gender;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TenantValidator
{
    /**
     * Dùng chung cho thêm / sửa hồ sơ khách (null = thêm mới). Ảnh CCCD validate cùng lúc.
     *
     * @param array $input
     * @param Tenant|null $tenant
     * @return \Illuminate\Validation\Validator
     */
    public function saveValidator(array $input, ?Tenant $tenant = null)
    {
        return Validator::make($input, [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'regex:'.User::PHONE_REGEX],
            'email' => ['nullable', 'email', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(Gender::values())],
            'id_number' => ['nullable', 'regex:/^\d{9}$|^\d{12}$/', Rule::unique('tenants', 'id_number')->ignore($tenant?->id)],
            'id_issued_date' => ['nullable', 'date', 'before_or_equal:today'],
            'id_issued_place' => ['nullable', 'string', 'max:255'],
            'permanent_address' => ['nullable', 'string', 'max:500'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:2000'],
            'id_front' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'id_back' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']
        ], self::messages());
    }

    /**
     * Message dùng chung với validator khai báo của chủ hộ / thành viên.
     *
     * @return array
     */
    public static function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'phone.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng 0.',
            'email.email' => 'Email không đúng định dạng.',
            'date_of_birth.date' => 'Ngày sinh không hợp lệ.',
            'date_of_birth.before' => 'Ngày sinh phải trước hôm nay.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'id_number.regex' => 'CCCD/CMND phải gồm 9 hoặc 12 chữ số.',
            'id_number.unique' => 'Số CCCD/CMND này đã có trong hệ thống.',
            'id_issued_date.before_or_equal' => 'Ngày cấp không được ở tương lai.',
            'id_front.image' => 'Ảnh mặt trước phải là tệp ảnh.',
            'id_front.mimes' => 'Ảnh mặt trước chỉ nhận JPG, PNG, WEBP.',
            'id_front.max' => 'Ảnh mặt trước tối đa 5MB.',
            'id_back.image' => 'Ảnh mặt sau phải là tệp ảnh.',
            'id_back.mimes' => 'Ảnh mặt sau chỉ nhận JPG, PNG, WEBP.',
            'id_back.max' => 'Ảnh mặt sau tối đa 5MB.'
        ];
    }
}
