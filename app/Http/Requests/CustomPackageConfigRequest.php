<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomPackageConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'base_fee' => ['required_if:enabled,true', 'nullable', 'numeric', 'min:0'],
            // Applied to every custom-package booking this photographer receives —
            // mirrors Package.buffer_minutes, which exists per fixed package instead.
            // Defaults to 0 (no buffer) when omitted; see UpdateCustomPackageConfigAction.
            'buffer_minutes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:480'],
        ];
    }
}