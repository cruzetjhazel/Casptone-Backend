<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;

class RequestBookingCancellationAction
{
    public function execute(Booking $booking, string $reason): Booking
    {
        if (! in_array($booking->status, [BookingStatus::Pending, BookingStatus::Accepted], true)) {
            throw ValidationException::withMessages([
                'status' => ['This booking cannot be cancelled in its current status.'],
            ]);
        }

        if ($booking->hasPendingCancellationRequest()) {
            throw ValidationException::withMessages([
                'status' => ['A cancellation request is already pending for this booking.'],
            ]);
        }

        $booking->update([
            'cancellation_reason' => $reason,
            'cancellation_requested_at' => now(),
            'cancellation_decision' => null,
            'cancellation_decided_at' => null,
        ]);

        return $booking->fresh();
    }
}