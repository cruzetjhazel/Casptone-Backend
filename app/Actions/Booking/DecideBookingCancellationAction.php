<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Enums\CancellationDecision;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;

class DecideBookingCancellationAction
{
    public function execute(Booking $booking, CancellationDecision $decision): Booking
    {
        if (! $booking->hasPendingCancellationRequest()) {
            throw ValidationException::withMessages([
                'status' => ['There is no pending cancellation request for this booking.'],
            ]);
        }

        $booking->cancellation_decision = $decision;
        $booking->cancellation_decided_at = now();

        if ($decision === CancellationDecision::Approved) {
            $booking->status = BookingStatus::Cancelled;
        }

        $booking->save();

        $fresh = $booking->fresh();

        if ($decision === CancellationDecision::Approved) {
            $fresh->client->notify(new \App\Notifications\Booking\BookingCancelledNotification($fresh));
            $fresh->photographer->notify(new \App\Notifications\Booking\BookingCancelledNotification($fresh));
        } else {
            $fresh->client->notify(new \App\Notifications\Booking\CancellationRequestRejectedNotification($fresh));
        }

        return $fresh;
    }
}