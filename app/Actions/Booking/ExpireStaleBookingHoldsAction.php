<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Models\Booking;

class ExpireStaleBookingHoldsAction
{
    public function execute(): int
    {
        return Booking::where('status', BookingStatus::Pending)
            ->where('hold_expires_at', '<', now())
            ->update([
                'status' => BookingStatus::Cancelled,
                'cancellation_reason' => 'Booking hold expired after 24 hours without a photographer decision.',
                'hold_expires_at' => null,
            ]);
    }
}