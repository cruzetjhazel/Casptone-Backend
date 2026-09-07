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
        if ($booking->status !== BookingStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => ['Only pending booking requests can be accepted.'],
            ]);
        }

        $booking->update([
            'status' => BookingStatus::Confirmed,
            'hold_expires_at' => null,
        ]);

        $fresh = $booking->fresh();
        $fresh->client->notify(new \App\Notifications\Booking\BookingAcceptedNotification($fresh));

        $this->activityLogger->execute(
            causer: $fresh->photographer,
            subject: $fresh,
            action: 'booking.accepted',
            description: "Accepted booking #{$fresh->id} for {$fresh->client->name}",
        );

        return $fresh;
    }
}