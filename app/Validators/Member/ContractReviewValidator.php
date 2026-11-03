<?php

namespace App\Validators\Member;

use Illuminate\Support\Facades\Validator;

class ContractReviewValidator
{
    /**
     * Chủ hộ yêu cầu sửa hợp đồng: bắt buộc nêu nội dung cần sửa.
     *
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function rejectValidator(array $input)
    {
        return Validator::make($input, [
            'reject_reason' => ['required', 'string', 'max:500']
        ], [
            'reject_reason.required' => 'Vui lòng ghi rõ nội dung cần sửa.',
            'reject_reason.max' => 'Nội dung tối đa 500 ký tự.'
        ]);
    }
}
