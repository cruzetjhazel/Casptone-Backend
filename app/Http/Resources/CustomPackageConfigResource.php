<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomPackageConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'enabled' => $this->enabled,
            'base_fee' => $this->base_fee,
            'buffer_minutes' => $this->buffer_minutes,
            'updated_at' => $this->updated_at,
        ];
    }
}