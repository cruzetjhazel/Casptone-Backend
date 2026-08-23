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
     * `hold_expires_at` is shared by two different holds, and both now lapse
     * to Expired — nobody actively cancelled either one, the deadline just
     * passed without the responsible party acting:
     *
     *  - Pending: the photographer never reviewed the request in time.
     *  - Accepted: the photographer said yes, but the client never paid
     *    within the payment window.
     *
     * Cancelled is reserved for cases where a client or photographer
     * actively cancels a booking (see DecideBookingCancellationAction) —
     * a timeout is never a cancellation.
     *
     * Each expired booking is logged individually (via LogActivityAction)
     * so it's traceable in Activity Logs the same way accept/reject already
     * are — this is why we loop instead of a single bulk update.
     */
    public function execute(): int
    {
        return $this->expirePendingRequests() + $this->expireUnpaidAcceptances();
    }

    private function expirePendingRequests(): int
    {
        $bookings = Booking::with('client')
            ->where('status', BookingStatus::Pending)
            ->where('hold_expires_at', '<', now())
            ->get();

        foreach ($bookings as $booking) {
            $booking->update([
                'status' => BookingStatus::Expired,
                // No dedicated `expiration_reason` column exists yet — reusing
                // `cancellation_reason` here is a pragmatic stopgap, not
                // semantically accurate (this isn't a cancellation). Worth a
                // real `expiration_reason` migration if this distinction
                // matters for reporting later.
                'cancellation_reason' => 'Request expired after 24 hours without a photographer decision.',
                'hold_expires_at' => null,
            ]);

            $this->activityLogger->execute(
                causer: null,
                subject: $booking,
                action: 'booking.expired',
                description: "Booking #{$booking->id} for {$booking->client->name} expired — no photographer decision within 24 hours.",
            );
        }

        return $bookings->count();
    }

    private function expireUnpaidAcceptances(): int
    {
        $bookings = Booking::with('client')
            ->where('status', BookingStatus::Accepted)
            ->where('hold_expires_at', '<', now())
            ->get();

        foreach ($bookings as $booking) {
            $booking->update([
                'status' => BookingStatus::Expired,
                'hold_expires_at' => null,
            ]);

            $this->activityLogger->execute(
                causer: null,
                subject: $booking,
                action: 'booking.expired',
                description: "Booking #{$booking->id} for {$booking->client->name} expired — payment not received within the window.",
            );
        }

        return $bookings->count();
    }
}