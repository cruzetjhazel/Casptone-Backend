<?php

namespace App\Actions\Booking;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\BookingStatus;
use App\Enums\ServiceTrackerStatus;
use App\Models\Booking;
use App\Notifications\Booking\ServiceTrackerUpdatedNotification;
use Illuminate\Validation\ValidationException;

class UpdateServiceTrackerStatusAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(Booking $booking, ServiceTrackerStatus $status): Booking
    {
        if (! $booking->canManageServiceTracker()) {
            throw ValidationException::withMessages([
                'status' => ['The service tracker is only available for a Confirmed booking.'],
            ]);
        }

        $booking->service_status = $status;
        $booking->service_status_updated_at = now();
        $booking->save();

        $fresh = $booking->fresh();
        $fresh->client->notify(new ServiceTrackerUpdatedNotification($fresh));

        $this->activityLogger->execute(
            causer: $fresh->photographer,
            subject: $fresh,
            action: 'booking.service_tracker_updated',
            description: "Updated service tracker for booking #{$fresh->id} to {$status->value}",
        );

        return $fresh;
    }
}