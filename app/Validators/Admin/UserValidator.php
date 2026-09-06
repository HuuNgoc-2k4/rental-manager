<?php

namespace App\Validators\Admin;

use App\Enums\User\UserRole;
use App\Enums\User\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserValidator
{
    /**
     * @param array $input
     * @param User $user
     * @return \Illuminate\Validation\Validator
     */
    public function updateValidator(array $input, User $user)
    {
        return Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'required_without:phone',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'phone' => [
                'nullable',
                'required_without:email',
                'regex:'.User::PHONE_REGEX,
                Rule::unique('users', 'phone')->ignore($user->id)
            ],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in(UserRole::values())],
            'status' => ['required', Rule::in(UserStatus::values())]
        ], $this->messages());
    }

    /**
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function updateStatusValidator(array $input)
    {
        return Validator::make($input, [
            'status' => ['required', Rule::in(UserStatus::values())]
        ], $this->messages());
    }

    /**
     * @return array
     */
    private function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required_without' => 'Vui lòng nhập email hoặc số điện thoại.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'phone.required_without' => 'Vui lòng nhập email hoặc số điện thoại.',
            'phone.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng 0.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.in' => 'Vai trò không hợp lệ.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.'
        ];
    }
}
