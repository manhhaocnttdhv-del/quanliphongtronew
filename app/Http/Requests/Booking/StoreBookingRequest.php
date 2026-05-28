<?php

namespace App\Http\Requests\Booking;

use App\Models\Room;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Chỉ cho phép user đã đăng nhập gửi yêu cầu đặt thuê.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Quy tắc validate cho form gửi yêu cầu đặt thuê phòng.
     */
    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'cccd' => ['required', 'string', 'regex:/^\d{12}$/'],
            'phone' => ['required', 'string', 'regex:/^0\d{9}$/'],
            'birthday' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other'],
            'address' => ['nullable', 'string', 'max:500'],
            'hometown' => ['nullable', 'string', 'max:255'],
            'desired_move_in_date' => ['required', 'date', 'after_or_equal:today'],
            'desired_occupants' => ['required', 'integer', 'min:1'],
            'desired_lease_months' => ['required', 'integer', 'between:1,36'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Thông báo lỗi tiếng Việt theo Requirement 4.
     */
    public function messages(): array
    {
        return [
            'cccd.regex' => 'Số CCCD phải gồm 12 chữ số',
            'phone.regex' => 'Số điện thoại không hợp lệ',
            'desired_move_in_date.after_or_equal' => 'Ngày chuyển vào phải từ hôm nay trở đi',
            'desired_lease_months.between' => 'Thời hạn thuê phải từ 1 đến 36 tháng',
        ];
    }

    /**
     * Inject room_id từ query string nếu chưa có trong body.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('room_id') && $this->query('room_id')) {
            $this->merge(['room_id' => (int) $this->query('room_id')]);
        }
    }

    /**
     * Cross-field validation: số người ở dự kiến không vượt quá max_occupants của phòng.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $roomId = $this->input('room_id');
            $occupants = (int) $this->input('desired_occupants', 0);

            if (! $roomId || ! $occupants) {
                return;
            }

            $room = Room::find($roomId);
            if ($room && $occupants > $room->max_occupants) {
                $v->errors()->add('desired_occupants', 'Số người ở vượt quá sức chứa của phòng');
            }
        });
    }
}
