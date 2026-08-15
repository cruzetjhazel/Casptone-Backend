<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;

class ExpireStaleBookingHoldsAction
{
    /**
     * `hold_expires_at` is shared by two different holds, which lapse to two
     * different outcomes:
     *
     *  - Pending: the photographer never reviewed the request in time — the
     *    request itself lapses. Goes to Cancelled (unchanged from before).
     *  - Accepted: the photographer said yes, but the client never paid
     *    within the payment window. Goes to Expired rather than Cancelled —
     *    the photographer held up their end; the date is released only
     *    because payment didn't arrive in time, which is a meaningfully
     *    different outcome for reporting/support purposes.
     */
    public function execute(): int
    {
        $expiredRequests = Booking::where('status', BookingStatus::Pending)
            ->where('hold_expires_at', '<', now())
            ->update([
                'status' => BookingStatus::Cancelled,
                'cancellation_reason' => 'Booking hold expired after 24 hours without a photographer decision.',
                'hold_expires_at' => null,
            ]);

        $expiredPayments = Booking::where('status', BookingStatus::Accepted)
            ->where('hold_expires_at', '<', now())
            ->update([
                'status' => BookingStatus::Expired,
                'hold_expires_at' => null,
            ]);

        return $expiredRequests + $expiredPayments;
    }
}