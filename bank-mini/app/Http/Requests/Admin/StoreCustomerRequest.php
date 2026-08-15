<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'max:20', 'unique:customers,nis'],
            'name' => ['required', 'string', 'max:100'],
            'class' => ['required', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah terdaftar.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'class.required' => 'Kelas wajib diisi.',
        ];
    }
}
