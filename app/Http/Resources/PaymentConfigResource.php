<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PaymentConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'gcash_account_name' => $this->gcash_account_name,
            'gcash_account_number' => $this->gcash_account_number,
            'gcash_qr_url' => Storage::disk('public')->url($this->gcash_qr_path),
            'updated_at' => $this->updated_at,
        ];
    }
}