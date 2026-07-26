<?php

namespace App\Notifications\Payment;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentRejectedNotification extends Notification
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
            'type' => 'payment.rejected',
            'booking_id' => $this->payment->booking_id,
            'payment_id' => $this->payment->id,
            'message' => 'Your payment submission was rejected. Please resubmit your payment details.',
            'notes' => $this->payment->verification_notes,
        ];
    }
}