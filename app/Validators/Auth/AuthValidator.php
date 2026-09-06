<?php

namespace App\Validators\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Validator;

class AuthValidator
{
    /**
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function loginValidator(array $input)
    {
        return Validator::make($input, [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string']
        ], [
            'login.required' => 'Vui lòng nhập email hoặc số điện thoại.',
            'password.required' => 'Vui lòng nhập mật khẩu.'
        ]);
    }

    /**
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function registerValidator(array $input)
    {
        return Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'required_without:email', 'regex:'.User::PHONE_REGEX, 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required_without' => 'Vui lòng nhập email hoặc số điện thoại.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'phone.required_without' => 'Vui lòng nhập email hoặc số điện thoại.',
            'phone.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng 0.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu tối thiểu 8 ký tự.',
            'password.confirmed' => 'Mật khẩu nhập lại không khớp.'
        ]);
    }
}
