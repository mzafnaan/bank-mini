<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:25', 'unique:users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:administrator,teller,supervisor'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
