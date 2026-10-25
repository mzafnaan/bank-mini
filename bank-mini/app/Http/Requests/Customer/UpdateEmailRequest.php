<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->guard('customer_web')->check();
    }

    public function rules(): array
    {
        $customerId = auth()->guard('customer_web')->user()?->customer_id;

        return [
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $customer = auth()->guard('customer_web')->user()?->customer;
            $newEmail = strtolower(trim((string) $this->input('email')));

            if ($customer && $customer->email && strtolower($customer->email) === $newEmail && $customer->email_verified_at) {
                $validator->errors()->add('email', 'Alamat email ini sudah menjadi email pemulihan aktif Anda.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.max' => 'Panjang email maksimal 100 karakter.',
            'email.unique' => 'Alamat email ini sudah digunakan oleh nasabah lain.',
        ];
    }
}
