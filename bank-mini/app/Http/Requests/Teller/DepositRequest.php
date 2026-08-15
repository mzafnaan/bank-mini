<?php

namespace App\Http\Requests\Teller;

use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'teller';
    }

    public function rules(): array
    {
        return [
            'account_number' => ['required', 'string', 'exists:bank_accounts,account_number'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.required' => 'Nomor rekening wajib diisi.',
            'account_number.exists' => 'Nomor rekening tidak ditemukan.',
            'amount.required' => 'Nominal setoran wajib diisi.',
            'amount.numeric' => 'Nominal setoran harus berupa angka.',
            'amount.min' => 'Nominal setoran minimal adalah Rp 1.000.',
        ];
    }
}
