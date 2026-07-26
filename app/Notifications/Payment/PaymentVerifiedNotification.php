<?php

namespace App\Notifications\Payment;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentVerifiedNotification extends Notification
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
            'type' => 'payment.verified',
            'booking_id' => $this->payment->booking_id,
            'payment_id' => $this->payment->id,
            'message' => 'Your GCash reference code was successfully matched and your payment has been verified.',
        ];
    }
}