<?php

namespace App\Notifications\Booking;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class ServiceTrackerUpdatedNotification extends Notification
{
    public function __construct(protected Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $stage = $this->booking->service_status;
        $stageValue = is_object($stage) && method_exists($stage, 'value') ? $stage->value : (string) $stage;
        $label = ucwords(str_replace('_', ' ', $stageValue));
        $eventLabel = $this->booking->custom_event_type ?: $this->booking->event_type;

        return [
            'type' => 'booking',
            'title' => 'Booking Status Updated',
            'description' => "Your {$eventLabel} booking is now: {$label}.",
            'booking_id' => (string) $this->booking->id,
            'action' => null,
        ];
    }
}