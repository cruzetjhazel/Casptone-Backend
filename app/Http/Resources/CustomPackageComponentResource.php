<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomPackageComponentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'tier_name' => $this->tier_name,
            'label' => $this->label,
            'price_addition' => $this->price_addition,
            'duration_minutes' => $this->duration_minutes,
            'status' => $this->status->value,
        ];
    }
}