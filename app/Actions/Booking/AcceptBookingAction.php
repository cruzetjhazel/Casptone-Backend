<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;

class AcceptBookingAction
{
    public function execute(Booking $booking): Booking
    {
        if ($booking->status !== BookingStatus::Pending || $booking->isHoldExpired()) {
            throw ValidationException::withMessages([
                'status' => ['Only pending, unexpired booking requests can be accepted.'],
            ]);
        }

        $booking->update(['status' => BookingStatus::Accepted, 'hold_expires_at' => null]);

        $fresh = $booking->fresh();
        $fresh->client->notify(new \App\Notifications\Booking\BookingAcceptedNotification($fresh));

        return $fresh;
    }
}