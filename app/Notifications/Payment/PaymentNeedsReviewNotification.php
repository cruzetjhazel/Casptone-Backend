<?php

namespace App\Notifications\Payment;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentNeedsReviewNotification extends Notification
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
            'type' => 'payment.needs_review',
            'booking_id' => $this->payment->booking_id,
            'payment_id' => $this->payment->id,
            'message' => 'A payment submission could not be automatically matched and needs your manual review.',
        ];
    }
}