<?php

namespace App\Actions\Payment;

use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\PaymentPlan;
use App\Enums\PaymentType;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Validation\ValidationException;

/**
 * Records a Client's submitted GCash payment and, per §8.3/§8.5, moves the
 * booking straight to Confirmed. There is no gateway callback in this flow
 * (§8.4 — "the system does not directly process the GCash transaction") —
 * the backend validates and *records* the submission, it does not verify
 * with GCash that funds actually moved.
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

        if ($booking->payment_status !== BookingPaymentStatus::Pending) {
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

        if (Payment::where('reference_number', $data['reference_number'])->exists()) {
            throw ValidationException::withMessages([
                'reference_number' => ['This GCash reference number has already been recorded.'],
            ]);
        }

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'client_id' => $booking->client_id,
            'photographer_id' => $booking->photographer_id,
            'type' => PaymentType::Online,
            'method' => 'gcash',
            'plan' => $plan->value,
            'amount' => $data['amount'],
            'reference_number' => $data['reference_number'],
            'payment_date' => $data['payment_date'],
        ]);

        $booking->update([
            'payment_plan' => $plan,
            'payment_status' => $plan === PaymentPlan::Full
                ? BookingPaymentStatus::FullyPaid
                : BookingPaymentStatus::PartiallyPaid,
            'status' => BookingStatus::Confirmed,
        ]);

        return $payment->fresh();
    }
}