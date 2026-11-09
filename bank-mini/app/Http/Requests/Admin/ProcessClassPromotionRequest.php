<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProcessClassPromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $decisions = $this->input('decisions', []);
        if (is_array($decisions)) {
            foreach ($decisions as $id => $item) {
                if (is_array($item)) {
                    if (isset($item['decision']) && !isset($item['action'])) {
                        $decisions[$id]['action'] = $item['decision'];
                    }
                    if (isset($item['action']) && !isset($item['decision'])) {
                        $decisions[$id]['decision'] = $item['action'];
                    }
                }
            }
            $this->merge(['decisions' => $decisions]);
        }
    }

    public function rules(): array
    {
        return [
            'target_academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],
            'source_academic_year_id' => [
                'nullable',
                'exists:academic_years,id',
                'different:target_academic_year_id',
            ],
            'decisions' => [
                'required',
                'array',
                'min:1',
            ],
            'decisions.*.action' => [
                'required',
                'string',
                'in:promote,stay,graduate',
            ],
            'decisions.*.target_class_group' => [
                'nullable',
                'string',
                'max:50',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'target_academic_year_id.required' => 'Tahun ajaran tujuan wajib dipilih.',
            'target_academic_year_id.exists' => 'Tahun ajaran tujuan tidak valid.',
            'source_academic_year_id.different' => 'Tahun ajaran tujuan tidak boleh sama dengan tahun ajaran asal.',
            'decisions.required' => 'Tidak ada data siswa yang diproses.',
            'decisions.min' => 'Pilih minimal satu siswa untuk diproses.',
            'decisions.*.action.required' => 'Keputusan kenaikan kelas/kelulusan wajib ditentukan.',
            'decisions.*.action.in' => 'Pilihan keputusan harus Naik Kelas, Tinggal Kelas, atau Lulus.',
        ];
    }
}

