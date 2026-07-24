<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'photographer_id' => ['required', 'integer', 'exists:users,id'],

            'is_custom_package' => ['sometimes', 'boolean'],
            'package_id' => ['required_if:is_custom_package,false', 'nullable', 'integer'],
            'custom_component_ids' => ['sometimes', 'array'],
            'custom_component_ids.*' => ['integer'],

            'add_on_ids' => ['sometimes', 'array'],
            'add_on_ids.*' => ['integer'],

            'event_type' => ['required', Rule::in([
                'wedding', 'birthday', 'prenup', 'graduation', 'portrait',
                'corporate_event', 'product_photography', 'family_event', 'other',
            ])],
            'custom_event_type' => ['required_if:event_type,other', 'nullable', 'string', 'max:255'],
            'event_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],

            'location_type' => ['required', Rule::in(['studio', 'client_location', 'outdoor_location', 'other'])],
            'event_address' => ['required_unless:location_type,studio', 'nullable', 'string', 'max:500'],
            'guest_count' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'special_requests' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}