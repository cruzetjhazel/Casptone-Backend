<?php

namespace App\Notifications\Payment;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentPendingVerificationNotification extends Notification
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
            'type' => 'payment.pending_verification',
            'booking_id' => $this->payment->booking_id,
            'payment_id' => $this->payment->id,
            'message' => 'Your submitted GCash reference could not be automatically matched. The photographer will review your payment.',
        ];
    }
}