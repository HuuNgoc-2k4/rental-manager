<?php

namespace App\Validators\Manage;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

class ContractValidator
{
    /**
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function rentOutValidator(array $input)
    {
        return Validator::make($input, [
            'tenant_id' => ['nullable', 'required_without:full_name', 'integer', 'exists:tenants,id'],
            'full_name' => ['nullable', 'required_without:tenant_id', 'string', 'max:255'],
            'phone' => ['nullable', 'regex:'.User::PHONE_REGEX],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'issue_account' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:2000']
        ], [
            'tenant_id.required_without' => 'Vui lòng chọn khách có sẵn hoặc nhập họ tên khách mới.',
            'tenant_id.exists' => 'Hồ sơ khách không tồn tại.',
            'full_name.required_without' => 'Vui lòng chọn khách có sẵn hoặc nhập họ tên khách mới.',
            'phone.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng 0.',
            'start_date.required' => 'Vui lòng chọn ngày vào ở.',
            'start_date.date' => 'Ngày vào ở không hợp lệ.',
            'end_date.after' => 'Ngày hết hạn phải sau ngày vào ở.'
        ]);
    }

    /**
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function addMemberValidator(array $input)
    {
        return Validator::make($input, [
            'tenant_id' => ['nullable', 'required_without:full_name', 'integer', 'exists:tenants,id'],
            'full_name' => ['nullable', 'required_without:tenant_id', 'string', 'max:255'],
            'phone' => ['nullable', 'regex:'.User::PHONE_REGEX]
        ], [
            'tenant_id.required_without' => 'Vui lòng chọn khách có sẵn hoặc nhập họ tên người mới.',
            'tenant_id.exists' => 'Hồ sơ khách không tồn tại.',
            'full_name.required_without' => 'Vui lòng chọn khách có sẵn hoặc nhập họ tên người mới.',
            'phone.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng 0.'
        ]);
    }

    /**
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function checkoutValidator(array $input)
    {
        return Validator::make($input, [
            'ended_at' => ['required', 'date', 'before_or_equal:today']
        ], [
            'ended_at.required' => 'Vui lòng chọn ngày trả phòng.',
            'ended_at.before_or_equal' => 'Ngày trả phòng không được ở tương lai.'
        ]);
    }
}
