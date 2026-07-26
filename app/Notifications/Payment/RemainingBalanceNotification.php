<?php

namespace App\Notifications\Payment;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RemainingBalanceNotification extends Notification
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
            'type' => 'payment.remaining_balance',
            'booking_id' => $this->booking->id,
            'remaining_balance' => (string) $this->booking->remainingBalance(),
            'message' => 'A remaining balance of '.number_format($this->booking->remainingBalance(), 2).' is due, payable onsite.',
        ];
    }
}