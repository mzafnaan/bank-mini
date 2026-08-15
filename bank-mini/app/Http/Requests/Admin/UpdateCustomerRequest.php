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
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }
}
