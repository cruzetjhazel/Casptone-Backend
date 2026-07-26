<?php

namespace App\Notifications\Payment;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(protected Payment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment.received',
            'booking_id' => $this->payment->booking_id,
            'payment_id' => $this->payment->id,
            'amount' => (string) $this->payment->amount,
            'message' => 'A payment was automatically verified for one of your bookings.',
        ];
    }
}