<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customerId = $this->route('customer') ? $this->route('customer')->id : null;

        return [
            'nis' => ['required', 'string', 'max:20', Rule::unique('customers', 'nis')->ignore($customerId)],
            'name' => ['required', 'string', 'max:100'],
            'class' => ['required', 'string', 'max:20'],
            'class_group' => ['nullable', 'string', 'max:50'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'status' => ['nullable', 'string', 'in:active,inactive,aktif,nonaktif'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9]+$/', 'min:9', 'max:15'],
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('customers', 'email')->ignore($customerId),
                Rule::unique('customers', 'pending_email')->ignore($customerId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah terdaftar.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'class.required' => 'Kelas wajib diisi.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka.',
            'phone.min' => 'Nomor telepon minimal 9 digit.',
            'phone.max' => 'Nomor telepon maksimal 15 digit.',
        ];
    }
}
