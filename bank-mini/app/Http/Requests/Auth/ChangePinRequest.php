<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_pin' => ['required', 'string', 'digits:6'],
            'new_pin' => ['required', 'string', 'digits:6', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_pin.required' => 'PIN saat ini wajib diisi.',
            'current_pin.digits' => 'PIN saat ini harus 6 digit angka.',
            'new_pin.required' => 'PIN baru wajib diisi.',
            'new_pin.digits' => 'PIN baru harus 6 digit angka.',
            'new_pin.confirmed' => 'Konfirmasi PIN baru tidak cocok.',
        ];
    }
}
