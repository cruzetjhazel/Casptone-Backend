<?php

namespace App\Actions\Booking;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\BookingStatus;
use App\Models\Booking;

class ExpireStaleBookingHoldsAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    /**
     * A Pending booking expires once its own event start time arrives
     * without the photographer having decided on it — there's no longer a
     * separate accepted/unpaid timer, since Accepted no longer exists and
     * booking status doesn't track payment anymore (see paymentStatus).
     */
    public function execute(): int
    {
        $bookings = Booking::with('client')
            ->where('status', BookingStatus::Pending)
            ->whereRaw("CONCAT(event_date, ' ', start_time) < ?", [now()->format('Y-m-d H:i:s')])
            ->get();

        foreach ($bookings as $booking) {
            $booking->update([
                'status' => BookingStatus::Expired,
                'cancellation_reason' => 'Request expired — no photographer decision before the event start time.',
                'hold_expires_at' => null,
            ]);

            $this->activityLogger->execute(
                causer: null,
                subject: $booking,
                action: 'booking.expired',
                description: "Booking #{$booking->id} for {$booking->client->name} expired — no photographer decision before event start.",
            );
        }

        return $bookings->count();
    }
}