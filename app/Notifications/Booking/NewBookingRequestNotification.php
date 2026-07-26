<?php

namespace App\Notifications\Booking;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewBookingRequestNotification extends Notification
{
    use Queueable;

    public function __construct(protected Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'booking.requested',
            'booking_id' => $this->booking->id,
            'message' => "New booking request from {$this->booking->client->name} for {$this->booking->event_date->format('Y-m-d')}.",
        ];
    }
}