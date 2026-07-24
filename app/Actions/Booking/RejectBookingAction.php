<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;

class RejectBookingAction
{
    public function execute(Booking $booking, string $reason): Booking
    {
        if ($booking->status !== BookingStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => ['Only pending booking requests can be rejected.'],
            ]);
        }

        $booking->update([
            'status' => BookingStatus::Rejected,
            'rejection_reason' => $reason,
            'hold_expires_at' => null,
        ]);

        return $booking->fresh();
    }
}