<?php

namespace App\Validators\Member;

use App\Enums\Tenant\Gender;
use App\Models\Tenant;
use App\Validators\Manage\TenantValidator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DeclarationValidator
{
    /**
     * Khai báo thông tin cá nhân. Hồ sơ đã xác minh chỉ nhận email / nghề nghiệp (phần còn lại bị khóa, không bắt buộc).
     *
     * @param array $input
     * @param Tenant $tenant
     * @return \Illuminate\Validation\Validator
     */
    public function submitValidator(array $input, Tenant $tenant)
    {
        $messages = TenantValidator::messages() + [
            'full_name.required' => 'Vui lòng nhập họ và tên.',
            'date_of_birth.required' => 'Vui lòng nhập ngày sinh.',
            'id_number.required' => 'Vui lòng nhập số CCCD/CMND.',
            'permanent_address.required' => 'Vui lòng nhập địa chỉ thường trú.',
            'consent.accepted' => 'Bạn cần đồng ý cho phép xử lý dữ liệu cá nhân.'
        ];

        $contact = [
            'email' => ['nullable', 'email', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255']
        ];

        if ($tenant->isVerified()) {
            return Validator::make($input, $contact, $messages);
        }

        return Validator::make($input, $contact + [
            'full_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['nullable', Rule::in(Gender::values())],
            'id_number' => ['required', 'regex:/^\d{9}$|^\d{12}$/', Rule::unique('tenants', 'id_number')->ignore($tenant->id)],
            'id_issued_date' => ['nullable', 'date', 'before_or_equal:today'],
            'id_issued_place' => ['nullable', 'string', 'max:255'],
            'permanent_address' => ['required', 'string', 'max:500'],
            'id_front' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'id_back' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            // Nghị định 13/2023: phải có sự đồng ý xử lý dữ liệu cá nhân trước khi gửi.
            'consent' => ['accepted']
        ], $messages);
    }
}
