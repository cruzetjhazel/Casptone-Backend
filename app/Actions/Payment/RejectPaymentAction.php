<?php

namespace App\Actions\Payment;

use App\Enums\BookingPaymentStatus;
use App\Enums\PaymentMatchingStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RejectPaymentAction
{
    public function execute(Payment $payment, User $verifier, string $notes): Payment
    {
        if (! $payment->isAwaitingManualReview()) {
            throw ValidationException::withMessages([
                'payment' => ['Only payments that failed automatic matching can be rejected.'],
            ]);
        }

        $payment->update([
            'matching_status' => PaymentMatchingStatus::Rejected,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'verification_action' => 'rejected',
            'verification_notes' => $notes,
        ]);

        // Reopen the booking for a fresh payment submission attempt.
        $payment->booking->update(['payment_status' => BookingPaymentStatus::Pending]);

        return $payment->fresh();
    }
}