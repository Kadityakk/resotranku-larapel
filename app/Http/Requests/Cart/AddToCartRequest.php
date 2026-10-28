<?php

namespace App\Http\Requests\Cart;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:items,id'],
            'qty' => ['sometimes', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function quantity(): int
    {
        return (int) ($this->validated('qty') ?? 1);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.exists' => 'Menu tidak ditemukan.',
        ];
    }
}
