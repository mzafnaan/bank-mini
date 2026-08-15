<?php

namespace App\Http\Requests\Teller;

use Illuminate\Foundation\Http\FormRequest;

class DailyReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'teller';
    }

    public function rules(): array
    {
        return [
            'physical_cash' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'physical_cash.required' => 'Jumlah uang fisik wajib diisi.',
            'physical_cash.numeric' => 'Jumlah uang fisik harus berupa angka.',
            'physical_cash.min' => 'Jumlah uang fisik tidak boleh negatif.',
        ];
    }
}
