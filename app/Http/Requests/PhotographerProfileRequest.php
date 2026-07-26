<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PhotographerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

        public function rules(): array
    {
        return [
            'bio' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'style' => ['sometimes', 'nullable', 'array'],
            'style.*' => ['string', 'max:255'],
            'facebook' => ['sometimes', 'nullable', 'url', 'max:255'],
            'instagram' => ['sometimes', 'nullable', 'url', 'max:255'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'profile_photo' => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'cover_photo' => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }
}