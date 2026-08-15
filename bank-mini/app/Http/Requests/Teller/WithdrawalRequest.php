<?php

namespace App\Http\Requests\Teller;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
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
            'pin' => ['required', 'digits:6'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_number.required' => 'Nomor rekening wajib diisi.',
            'account_number.exists' => 'Nomor rekening tidak ditemukan.',
            'amount.required' => 'Nominal penarikan wajib diisi.',
            'amount.numeric' => 'Nominal penarikan harus berupa angka.',
            'amount.min' => 'Nominal penarikan minimal adalah Rp 1.000.',
            'pin.required' => 'PIN otorisasi nasabah wajib dimasukkan.',
            'pin.digits' => 'PIN otorisasi nasabah harus 6 digit angka.',
        ];
    }
}
