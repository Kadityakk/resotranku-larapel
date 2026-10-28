<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateEmployeeRequest extends StoreEmployeeRequest
{
    /**
     * Password is optional on update; the unique checks must ignore this user.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');

        return [
            ...parent::rules(),
            'username' => [
                'required', 'string', 'alpha_dash', 'max:50',
                Rule::unique('users', 'username')->ignore($employee)->whereNull('deleted_at'),
            ],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($employee)->whereNull('deleted_at'),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }
}
