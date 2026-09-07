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
            // Optional — only set on the specific option(s) meant to represent a
            // selectable photography coverage duration (e.g. a "Coverage Duration"
            // tier with an option like "2 hours" = 120). Every other component
            // (photo tiers, delivery tiers, flat add-ons) leaves this null.
            // CreateBookingAction::resolveCustomPackage() uses the presence of a
            // non-null value on the client's selected components to determine
            // how long to reserve the photographer for — see that class for why
            // this can never be guessed/defaulted.
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ];
    }
}