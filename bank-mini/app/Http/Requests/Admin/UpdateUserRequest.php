<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;

        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:25', Rule::unique('users', 'username')->ignore($userId)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:administrator,teller,supervisor'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
