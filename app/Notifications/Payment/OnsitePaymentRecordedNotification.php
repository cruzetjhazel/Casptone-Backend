<?php

namespace App\Notifications\Payment;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OnsitePaymentRecordedNotification extends Notification
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
            'type' => 'payment.onsite_recorded',
            'booking_id' => $this->payment->booking_id,
            'payment_id' => $this->payment->id,
            'amount' => (string) $this->payment->amount,
            'message' => 'An onsite payment of '.number_format((float) $this->payment->amount, 2).' was recorded for this booking.',
        ];
    }
}