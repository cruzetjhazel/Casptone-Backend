<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplyToReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ownership + one-reply-only enforced via policy + action
    }

    public function rules(): array
    {
        return [
            // 300 matches MAX_REPLY_LENGTH already enforced client-side in StudioReviews.tsx.
            'reply' => ['required', 'string', 'max:300'],
        ];
    }
}