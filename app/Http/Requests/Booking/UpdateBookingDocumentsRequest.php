<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingDocumentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'cccd_front' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'cccd_back'  => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'cccd_front.mimes' => 'Chỉ chấp nhận ảnh định dạng JPG, PNG hoặc WEBP',
            'cccd_back.mimes'  => 'Chỉ chấp nhận ảnh định dạng JPG, PNG hoặc WEBP',
            'cccd_front.max'   => 'Dung lượng ảnh tối đa là 5 MB',
            'cccd_back.max'    => 'Dung lượng ảnh tối đa là 5 MB',
        ];
    }
}
