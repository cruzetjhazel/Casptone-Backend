<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ownership + one-report-only enforced via policy in the controller
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}