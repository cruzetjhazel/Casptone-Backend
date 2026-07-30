<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalkInClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => 'W-' . $this->id, // prefixed so it can't collide with registered-client user IDs in a merged list
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'location' => $this->location,
            // Walk-in clients have no bookings/spend on the platform by definition (Rule 48).
            'bookings' => 0,
            'spent' => 0,
            'type' => 'walk-in',
            'status' => $this->status,
            'source' => $this->source,
            'joined_year' => (int) $this->created_at->format('Y'),
        ];
    }
}