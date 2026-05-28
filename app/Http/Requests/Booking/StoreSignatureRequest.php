<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class StoreSignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'signature_data' => ['required', 'string', 'starts_with:data:image/png;base64,'],
            'agreed' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'signature_data.starts_with' => 'Chữ ký phải là ảnh PNG được mã hóa base64.',
            'agreed.accepted' => 'Bạn cần đồng ý các điều khoản trước khi ký.',
        ];
    }
}
