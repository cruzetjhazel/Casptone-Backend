<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhotographerPaymentReferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'amount_received' => $this->amount_received,
            'payment_date' => $this->payment_date->format('Y-m-d'),
            'status' => $this->status->value,
            'created_at' => $this->created_at,
        ];
    }
}