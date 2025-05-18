<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'exists:trips,id'],
            'seats' => ['required', 'array', 'min:1'],
            'seats.*' => ['required', 'exists:seats,id'],
            'passenger_name' => ['required', 'string', 'max:255'],
            'passenger_phone' => ['required', 'string', 'regex:/^([0-9\s\-\+\(\)]*)$/', 'min:10'],
            'passenger_email' => ['required', 'email'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', 'in:vnpay,momo,cash'],
        ];
    }

    public function messages(): array
    {
        return [
            'trip_id.required' => 'Vui lòng chọn chuyến xe',
            'trip_id.exists' => 'Chuyến xe không tồn tại',
            'seats.required' => 'Vui lòng chọn ghế',
            'seats.array' => 'Danh sách ghế không hợp lệ',
            'seats.min' => 'Vui lòng chọn ít nhất 1 ghế',
            'seats.*.exists' => 'Ghế không tồn tại',
            'passenger_name.required' => 'Vui lòng nhập tên hành khách',
            'passenger_phone.required' => 'Vui lòng nhập số điện thoại',
            'passenger_phone.regex' => 'Số điện thoại không hợp lệ',
            'passenger_email.required' => 'Vui lòng nhập email',
            'passenger_email.email' => 'Email không hợp lệ',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ',
        ];
    }
}
