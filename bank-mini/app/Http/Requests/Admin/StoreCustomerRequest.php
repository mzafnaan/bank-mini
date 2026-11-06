<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('initial_deposit')) {
            $cleaned = str_replace(['.', ',', ' '], '', (string) $this->input('initial_deposit'));
            $this->merge([
                'initial_deposit' => is_numeric($cleaned) ? (float) $cleaned : $this->input('initial_deposit'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'max:20', 'unique:customers,nis'],
            'name' => ['required', 'string', 'max:100'],
            'class' => ['required', 'string', 'max:20'],
            'major' => ['nullable', 'string', 'in:AK,BR,BD,MP,DKV,RPL,TKJ'],
            'phone' => ['nullable', 'string', 'max:20'],
            'initial_deposit' => ['required', 'numeric', 'gte:10000'],
            'teller_id' => ['nullable', 'exists:users,id'],
            'email' => [
                'nullable',
                'email',
                'max:100',
                Rule::unique('customers', 'email'),
                Rule::unique('customers', 'pending_email'),
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
            'initial_deposit.required' => 'Setoran awal wajib diisi untuk membuka rekening.',
            'initial_deposit.numeric' => 'Setoran awal harus berupa angka.',
            'initial_deposit.gte' => 'Setoran awal minimal Rp 10.000 untuk dapat membuka rekening.',
        ];
    }
}
