<?php

namespace App\Validators\Manage;

use Illuminate\Support\Facades\Validator;

class PropertyValidator
{
    /**
     * Dùng chung cho thêm và sửa khu.
     *
     * @param array $input
     * @return \Illuminate\Validation\Validator
     */
    public function saveValidator(array $input)
    {
        return Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:2000']
        ], [
            'name.required' => 'Vui lòng nhập tên khu.',
            'name.max' => 'Tên khu tối đa 255 ký tự.',
            'address.required' => 'Vui lòng nhập địa chỉ.',
            'address.max' => 'Địa chỉ tối đa 500 ký tự.',
            'note.max' => 'Ghi chú tối đa 2000 ký tự.'
        ]);
    }
}
