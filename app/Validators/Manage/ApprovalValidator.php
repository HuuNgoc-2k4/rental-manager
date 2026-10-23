<?php

namespace App\Validators\Manage;

use Illuminate\Support\Facades\Validator;

class ApprovalValidator
{
    /**
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function rejectValidator(array $input)
    {
        return Validator::make($input, [
            'reason' => ['required', 'string', 'max:500']
        ], [
            'reason.required' => 'Vui lòng nhập lý do từ chối / trả lại.',
            'reason.max' => 'Lý do tối đa 500 ký tự.'
        ]);
    }
}
