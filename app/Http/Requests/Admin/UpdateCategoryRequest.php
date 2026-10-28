<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'cat_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'cat_name')->ignore($this->route('category'))->whereNull('deleted_at'),
            ],
        ];
    }
}
