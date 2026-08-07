<?php

namespace App\Actions\Booking;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;

class AcceptBookingAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

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

        $this->activityLogger->execute(
            causer: $fresh->photographer,
            subject: $fresh,
            action: 'booking.accepted',
            description: "Accepted booking #{$fresh->id}",
        );

        return $fresh;
    }
}