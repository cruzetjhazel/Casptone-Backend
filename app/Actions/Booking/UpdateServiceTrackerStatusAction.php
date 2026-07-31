<?php

namespace App\Actions\Booking;

use App\Enums\BookingStatus;
use App\Enums\ServiceTrackerStatus;
use App\Models\Booking;
use App\Notifications\Booking\ServiceTrackerUpdatedNotification;
use Illuminate\Validation\ValidationException;

class UpdateServiceTrackerStatusAction
{
    public function execute(Booking $booking, ServiceTrackerStatus $status): Booking
    {
        if (! $booking->canManageServiceTracker()) {
            throw ValidationException::withMessages([
                'status' => ['The service tracker is only available for a Confirmed (or already Completed) booking.'],
            ]);
        }

        $wasCompleted = $booking->status === BookingStatus::Completed;

        $booking->service_status = $status;
        $booking->service_status_updated_at = now();

        // §8.11/§7.23: reaching the tracker's final stage is what completes
        // the booking (and makes it review-eligible). Moving the tracker back
        // out of Completed un-completes the booking symmetrically.
        if ($status === ServiceTrackerStatus::Completed) {
            $booking->status = BookingStatus::Completed;
        } elseif ($wasCompleted) {
            $booking->status = BookingStatus::Confirmed;
        }

        $booking->save();

        // §8.14-style client notification whenever the tracker moves.
        $booking->client->notify(new ServiceTrackerUpdatedNotification($booking));

        return $booking->fresh();
    }
}