<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserManagementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'account_type' => $this->account_type?->value ?? $this->account_type,
            'account_status' => $this->account_status?->value ?? $this->account_status,
            'created_at' => $this->created_at,
            'application_status' => $this->whenLoaded('photographerApplication', fn () =>
                $this->photographerApplication?->status?->value
            ),
            'photographer_type' => $this->whenLoaded('photographerApplication', fn () =>
                $this->photographerApplication?->photographer_type?->value
            ),
        ];
    }
}