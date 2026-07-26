<?php

namespace App\Notifications\Payment;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FullPaymentCompletedNotification extends Notification
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
            'type' => 'payment.fully_paid',
            'booking_id' => $this->booking->id,
            'message' => 'This booking has been paid in full.',
        ];
    }
}