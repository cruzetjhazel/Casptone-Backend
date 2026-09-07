<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServiceTrackerStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ownership + booking-state enforced via policy + action
    }

    public function rules(): array
    {
        return [
            'service_status' => ['required', Rule::in(['event_day', 'editing', 'delivered'])],
        ];
    }
}