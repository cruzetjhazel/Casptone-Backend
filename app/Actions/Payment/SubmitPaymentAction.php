<?php

namespace App\Actions\Payment;

use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentMatchingStatus;
use App\Enums\PaymentPlan;
use App\Enums\PaymentType;
use App\Enums\PhotographerPaymentReferenceStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PhotographerPaymentReference;
use Illuminate\Validation\ValidationException;

/**
 * Records a Client's submitted GCash payment and matches it against a
 * Photographer-recorded reference (§9.2/§9.3). Match -> Payment Verified,
 * Booking Confirmed (§8.11). No match -> Payment sits Not Matched, booking
 * moves to Pending Verification and stays Accepted — it does NOT auto-confirm
 * (§8.5.2/§9.4). The Photographer then manually verifies or rejects it.
 */
class SubmitPaymentAction
{
    public function execute(Booking $booking, array $data): Payment
    {
        if ($booking->status !== BookingStatus::Accepted) {
            throw ValidationException::withMessages([
                'booking' => ['This booking is not currently awaiting payment.'],
            ]);
        }

        if (! in_array($booking->payment_status, [BookingPaymentStatus::Pending], true)) {
            throw ValidationException::withMessages([
                'booking' => ['A payment has already been submitted for this booking.'],
            ]);
        }

        $plan = PaymentPlan::from($data['plan']);
        $expectedAmount = $booking->onlineAmountDueFor($plan);

        if (round((float) $data['amount'], 2) !== round($expectedAmount, 2)) {
            throw ValidationException::withMessages([
                'amount' => ["The amount paid must match the {$plan->value} payment amount of ".number_format($expectedAmount, 2).'.'],
            ]);
        }

        // §9.3 — primary matching key is Photographer ID + GCash Reference Code,
        // and the reference must not already be used for another booking.
        $match = PhotographerPaymentReference::where('photographer_id', $booking->photographer_id)
            ->where('reference_number', $data['reference_number'])
            ->where('status', PhotographerPaymentReferenceStatus::Available)
            ->first();

        $matched = $match !== null && round((float) $match->amount_received, 2) === round((float) $data['amount'], 2);

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'client_id' => $booking->client_id,
            'photographer_id' => $booking->photographer_id,
            'type' => PaymentType::Online,
            'method' => 'gcash',
            'plan' => $plan->value,
            'amount' => $data['amount'],
            'reference_number' => $data['reference_number'],
            'payer_name' => $data['payer_name'],
            'payment_date' => $data['payment_date'],
            'photographer_payment_reference_id' => $matched ? $match->id : null,
            'matching_status' => $matched ? PaymentMatchingStatus::Matched : PaymentMatchingStatus::NotMatched,
        ]);

        if ($matched) {
            $match->update(['status' => PhotographerPaymentReferenceStatus::Used]);

            $booking->update([
                'payment_plan' => $plan,
                'payment_status' => $plan === PaymentPlan::Full
                    ? BookingPaymentStatus::FullyPaid
                    : BookingPaymentStatus::PartiallyPaid,
                'status' => BookingStatus::Confirmed,
            ]);
        } else {
            $booking->update([
                'payment_plan' => $plan,
                'payment_status' => BookingPaymentStatus::PendingVerification,
            ]);
        }

        return $payment->fresh();
    }
}