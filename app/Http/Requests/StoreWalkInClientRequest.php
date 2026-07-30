<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWalkInClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'source' => ['required', Rule::in(['facebook', 'messenger', 'phone_call', 'walk_in', 'referral'])],
        ];
    }
}