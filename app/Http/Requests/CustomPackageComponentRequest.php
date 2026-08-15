<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomPackageComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['flat_option', 'tier_option'])],
            'tier_name' => ['required_if:type,tier_option', 'nullable', 'string', 'max:255'],
            'label' => ['required', 'string', 'max:255'],
            'price_addition' => ['required', 'numeric', 'min:0'],
        ];
    }
}