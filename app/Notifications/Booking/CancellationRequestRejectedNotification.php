<?php

namespace App\Notifications\Booking;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CancellationRequestRejectedNotification extends Notification
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
            'type' => 'booking.cancellation_request_rejected',
            'booking_id' => $this->booking->id,
            'message' => 'Your cancellation request was declined. The booking remains active.',
        ];
    }
}