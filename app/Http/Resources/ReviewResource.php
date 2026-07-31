<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_id' => $this->booking_id,
            'client' => ['id' => $this->client->id, 'name' => $this->client->name],
            'photographer' => ['id' => $this->photographer->id, 'name' => $this->photographer->name],
            'event_type' => $this->booking->custom_event_type ?: $this->booking->event_type,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'reply' => $this->reply,
            'replied_at' => $this->replied_at,
            'reported_at' => $this->reported_at,
            'created_at' => $this->created_at,
        ];
    }
}