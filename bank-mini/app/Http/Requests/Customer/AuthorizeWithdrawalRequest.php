<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class AuthorizeWithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('customer_web')->check();
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'string', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'pin.required' => 'PIN otorisasi 6 digit wajib dimasukkan.',
            'pin.digits' => 'PIN otorisasi harus terdiri dari 6 digit angka.',
        ];
    }
}
