<?php

namespace App\Notifications\Booking;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingRequestSubmittedNotification extends Notification
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
            'type' => 'booking.submitted',
            'booking_id' => $this->booking->id,
            'message' => "Your booking request for {$this->booking->event_date->format('Y-m-d')} has been submitted and is awaiting a response.",
        ];
    }
}