<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking' => [
                'id' => $this->booking->id,
                'status' => $this->booking->status->value,
                'payment_plan' => $this->booking->payment_plan?->value,
                'payment_status' => $this->booking->payment_status->value,
                'total_price' => $this->booking->total_price,
                'remaining_balance' => $this->booking->remainingBalance(),
            ],
            'client' => ['id' => $this->client->id, 'name' => $this->client->name],
            'photographer' => ['id' => $this->photographer->id, 'name' => $this->photographer->name],
            'type' => $this->type->value,
            'method' => $this->method,
            'plan' => $this->plan->value,
            'amount' => $this->amount,
            'reference_number' => $this->reference_number,
            'payment_date' => $this->payment_date->format('Y-m-d'),
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}