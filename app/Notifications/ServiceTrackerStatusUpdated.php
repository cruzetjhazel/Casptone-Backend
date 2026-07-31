<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ServiceTrackerStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(private readonly Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'service_tracker_updated',
            'booking_id' => $this->booking->id,
            'service_status' => $this->booking->service_status->value,
            'message' => 'Your booking service status is now: '
                . str_replace('_', ' ', $this->booking->service_status->value) . '.',
        ];
    }
}