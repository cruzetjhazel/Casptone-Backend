<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gcash_account_name' => ['required', 'string', 'max:150'],
            'gcash_account_number' => ['required', 'string', 'max:20'],
            // Not required on every submission — a photographer updating their
            // account name/number shouldn't be forced to re-upload the QR.
            // The controller enforces it's present on first-time creation.
            'gcash_qr_code' => ['sometimes', 'image', 'max:4096'],
        ];
    }
}