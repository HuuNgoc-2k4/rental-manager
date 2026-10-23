<?php

namespace App\Validators\Auth;

use Illuminate\Support\Facades\Validator;

class PasswordValidator
{
    /**
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function updateValidator(array $input)
    {
        return Validator::make($input, [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password']
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới tối thiểu 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'password.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.'
        ]);
    }
}
