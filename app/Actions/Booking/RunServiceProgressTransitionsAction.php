<?php

namespace App\Actions\Booking;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\BookingStatus;
use App\Enums\ServiceTrackerStatus;
use App\Models\Booking;

class RunServiceProgressTransitionsAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(): array
    {
        return [
            'moved_to_event_day' => $this->markEventDay(),
            'completed' => $this->markCompleted(),
        ];
    }

    private function markEventDay(): int
    {
        $bookings = Booking::with('client')
            ->where('status', BookingStatus::Confirmed)
            ->whereNull('service_status')
            ->whereRaw("CONCAT(event_date, ' ', start_time) <= ?", [now()->format('Y-m-d H:i:s')])
            ->get();

        foreach ($bookings as $booking) {
            $booking->update([
                'service_status' => ServiceTrackerStatus::EventDay,
                'service_status_updated_at' => now(),
            ]);

            $this->activityLogger->execute(
                causer: null,
                subject: $booking,
                action: 'booking.service_tracker_updated',
                description: "Booking #{$booking->id} automatically moved to Event Day.",
            );
        }

        return $bookings->count();
    }

    /**
     * Coverage end = event start + duration_minutes ONLY — never + buffer.
     * duration_minutes lives in package_snapshot (fixed) or
     * custom_package_snapshot (custom); both are set at booking creation
     * time by CreateBookingAction, so this never depends on the live
     * Package/CustomPackageConfig row.
     */
    private function markCompleted(): int
    {
        $bookings = Booking::with('client')
            ->where('status', BookingStatus::Confirmed)
            ->get()
            ->filter(function (Booking $booking) {
                $durationMinutes = $booking->is_custom_package
                    ? (int) ($booking->custom_package_snapshot['duration_minutes'] ?? 0)
                    : (int) ($booking->package_snapshot['duration_minutes'] ?? 0);

                if ($durationMinutes <= 0) {
                    return false;
                }

                $coverageEnd = \Carbon\Carbon::parse("{$booking->event_date->format('Y-m-d')} {$booking->start_time}")
                    ->addMinutes($durationMinutes);

                return $coverageEnd->lte(now());
            });

        foreach ($bookings as $booking) {
            $booking->update(['status' => BookingStatus::Completed]);

            $this->activityLogger->execute(
                causer: null,
                subject: $booking,
                action: 'booking.completed',
                description: "Booking #{$booking->id} automatically marked Completed — photography coverage ended.",
            );
        }

        return $bookings->count();
    }
}