<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplyReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ownership + one-reply-only enforced via policy in the controller
    }

    public function rules(): array
    {
        return [
            'reply' => ['required', 'string', 'max:300'],
        ];
    }
}