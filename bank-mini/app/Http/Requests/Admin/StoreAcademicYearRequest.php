<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                'unique:academic_years,name',
                'regex:/^\d{4}\/\d{4}$/',
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tahun ajaran wajib diisi.',
            'name.unique' => 'Nama tahun ajaran sudah ada dalam sistem.',
            'name.regex' => 'Format nama tahun ajaran harus YYYY/YYYY (contoh: 2026/2027).',
            'start_date.required' => 'Tanggal mulai tahun ajaran wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.required' => 'Tanggal selesai tahun ajaran wajib diisi.',
            'end_date.date' => 'Format tanggal selesai tidak valid.',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ];
    }
}

