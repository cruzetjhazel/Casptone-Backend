<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordOnsitePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }
}