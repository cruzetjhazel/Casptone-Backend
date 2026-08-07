<?php

namespace App\Actions\Payment;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\BookingPaymentStatus;
use App\Enums\PaymentType;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Validation\ValidationException;

/**
 * Records the onsite remaining balance for a Half-Payment booking (§8.9).
 * The system records the transaction; it does not process the payment itself.
 */
class RecordOnsitePaymentAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(Booking $booking, array $data): Payment
    {
        if (! $booking->isEligibleForOnsitePayment()) {
            throw ValidationException::withMessages([
                'booking' => ['This booking has no pending onsite balance.'],
            ]);
        }

        $remaining = $booking->remainingBalance();

        if (round((float) $data['amount'], 2) !== round($remaining, 2)) {
            throw ValidationException::withMessages([
                'amount' => ['The amount must match the remaining balance of '.number_format($remaining, 2).'.'],
            ]);
        }

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'client_id' => $booking->client_id,
            'photographer_id' => $booking->photographer_id,
            'type' => PaymentType::Onsite,
            'method' => 'cash',
            'plan' => $booking->payment_plan->value,
            'amount' => $data['amount'],
            'reference_number' => null,
            'payment_date' => $data['payment_date'],
            'notes' => $data['notes'] ?? null,
        ]);

        $booking->update(['payment_status' => BookingPaymentStatus::FullyPaid]);

        $freshBooking = $booking->fresh();
        $freshPayment = $payment->fresh();

        $freshBooking->client->notify(new \App\Notifications\Payment\OnsitePaymentRecordedNotification($freshPayment));
        $freshBooking->client->notify(new \App\Notifications\Payment\FullPaymentCompletedNotification($freshBooking));
        $freshBooking->photographer->notify(new \App\Notifications\Payment\FullPaymentCompletedNotification($freshBooking));

        $this->activityLogger->execute(
            causer: $freshBooking->photographer,
            subject: $freshPayment,
            action: 'payment.onsite_recorded',
            description: "Recorded onsite payment for booking #{$freshBooking->id}",
            metadata: ['amount' => $data['amount']],
        );

        return $freshPayment;
    }
}