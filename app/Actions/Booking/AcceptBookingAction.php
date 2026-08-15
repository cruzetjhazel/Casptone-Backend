<?php

namespace App\Actions\Booking;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Validation\ValidationException;

class AcceptBookingAction
{
    // How long a client has to submit payment after the photographer accepts
    // before the hold on the date is released automatically. Distinct from
    // the 24h review window CreateBookingAction sets on the initial request.
    private const PAYMENT_HOLD_HOURS = 48;

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

        $booking->update([
            'status' => BookingStatus::Accepted,
            'hold_expires_at' => now()->addHours(self::PAYMENT_HOLD_HOURS),
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