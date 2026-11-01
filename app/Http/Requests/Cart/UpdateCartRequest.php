<?php

namespace App\Http\Requests\Cart;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The order bar sends an absolute quantity, a message for the line, or both;
     * a quantity of zero removes the line. An empty message is how a guest
     * clears it, so the note only has to be sent, not filled.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer'],
            'qty' => ['sometimes', 'integer', 'min:0', 'max:99'],
            'note' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->has('qty') && ! $this->has('note')) {
                    $validator->errors()->add('qty', 'Jumlah atau pesan harus diisi.');
                }
            },
        ];
    }

    public function hasNote(): bool
    {
        return $this->has('note');
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'qty' => 'jumlah',
            'note' => 'pesan',
        ];
    }
}
