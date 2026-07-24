<?php

namespace App\Actions\Payment;

use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentMatchingStatus;
use App\Enums\PaymentPlan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Validation\ValidationException;

/**
 * §8.11 Manual Payment Review — used when automatic matching fails but the
 * Photographer confirms the payment was genuinely received.
 */
class ManuallyVerifyPaymentAction
{
    public function execute(Payment $payment, User $verifier, ?string $notes): Payment
    {
        if (! $payment->isAwaitingManualReview()) {
            throw ValidationException::withMessages([
                'payment' => ['Only payments that failed automatic matching can be manually verified.'],
            ]);
        }

        $payment->update([
            'matching_status' => PaymentMatchingStatus::ManuallyVerified,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
            'verification_action' => 'verified',
            'verification_notes' => $notes,
        ]);

        $booking = $payment->booking;
        $plan = $payment->plan;

        $booking->update([
            'payment_status' => $plan === PaymentPlan::Full
                ? BookingPaymentStatus::FullyPaid
                : BookingPaymentStatus::PartiallyPaid,
            'status' => BookingStatus::Confirmed,
        ]);

        return $payment->fresh();
    }
}