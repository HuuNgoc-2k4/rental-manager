<?php

namespace App\Validators\Auth;

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
}
