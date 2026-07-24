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
            'type' => ['required', Rule::in(['flat_option', 'photo_count_tier', 'delivery_duration_tier'])],
            'label' => ['required', 'string', 'max:255'],
            'price_addition' => ['required', 'numeric', 'min:0'],
        ];
    }
}