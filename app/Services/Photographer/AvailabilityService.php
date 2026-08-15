<?php

namespace App\Services\Photographer;

use App\Enums\BookingStatus;
use App\Models\AvailabilityWindow;
use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

/**
 * A date is bookable by default — it only becomes unavailable if the
 * photographer has explicitly blocked it (BlockedDate, full-day or partial)
 * or an active Booking already occupies the requested time. Approval of any
 * booking request is still entirely up to the photographer; this service
 * only determines whether a request can be *sent* for a given date/time.
 *
 * AvailabilityWindow is optional. If a photographer has set one for a date,
 * it narrows that date's bookable hours (e.g. "only 9am-5pm"). If there's no
 * window, the whole day (00:00-24:00) is treated as open, subject only to
 * blocks and bookings.
 */
class AvailabilityService
{
    // Bookings in these statuses actually hold the calendar slot.
    private const BLOCKING_BOOKING_STATUSES = [
        BookingStatus::Pending,
        BookingStatus::Accepted,
        BookingStatus::Confirmed,
    ];

    private const SLOT_STEP_MINUTES = 30;

    /**
     * Per-day availability for a month.
     *
     * @return array<string, "past"|"available"|"unavailable"> keyed by "Y-m-d"
     */
    public function getMonthSummary(User $photographer, string $start, string $end, int $durationMinutes): array
    {
        $windows = AvailabilityWindow::query()
            ->where('user_id', $photographer->id)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->keyBy(fn ($w) => $w->date->format('Y-m-d'));

        $blocksByDate = BlockedDate::query()
            ->where('user_id', $photographer->id)
            ->whereBetween('date', [$start, $end])
            ->get()
            ->groupBy(fn ($b) => $b->date->format('Y-m-d'));

        $bookingsByDate = Booking::query()
            ->where('photographer_id', $photographer->id)
            ->whereIn('status', self::BLOCKING_BOOKING_STATUSES)
            ->whereBetween('event_date', [$start, $end])
            ->get()
            ->groupBy(fn ($b) => $b->event_date->format('Y-m-d'));

        $summary = [];
        $today = Carbon::today();

        foreach (CarbonPeriod::create($start, $end) as $day) {
            $dateStr = $day->format('Y-m-d');

            if ($day->lt($today)) {
                $summary[$dateStr] = 'past';
                continue;
            }

            $window = $windows->get($dateStr);
            $dayBlocks = $blocksByDate->get($dateStr, collect());
            $dayBookings = $bookingsByDate->get($dateStr, collect());

            $summary[$dateStr] = $this->hasAvailableStartTime($dateStr, $window, $dayBlocks, $dayBookings, $durationMinutes)
                ? 'available'
                : 'unavailable';
        }

        return $summary;
    }

    /**
     * All valid booking start times on a single date for a given duration.
     *
     * @return string[] "HH:mm" values, in SLOT_STEP_MINUTES increments
     */
    public function getAvailableStartTimes(User $photographer, string $date, int $durationMinutes): array
    {
        $window = AvailabilityWindow::query()
            ->where('user_id', $photographer->id)
            ->where('date', $date)
            ->first();

        $blocks = BlockedDate::query()
            ->where('user_id', $photographer->id)
            ->where('date', $date)
            ->get();

        $bookings = Booking::query()
            ->where('photographer_id', $photographer->id)
            ->whereIn('status', self::BLOCKING_BOOKING_STATUSES)
            ->where('event_date', $date)
            ->get();

        return $this->availableStartTimes($date, $window, $blocks, $bookings, $durationMinutes);
    }

    private function hasAvailableStartTime(string $dateStr, ?AvailabilityWindow $window, Collection $blocks, Collection $bookings, int $durationMinutes): bool
    {
        return count($this->availableStartTimes($dateStr, $window, $blocks, $bookings, $durationMinutes)) > 0;
    }

    /**
     * Walks the day (or the narrower window, if one is set) in
     * SLOT_STEP_MINUTES increments and keeps any start time whose
     * [start, start+duration] doesn't overlap a block or booking.
     */
    private function availableStartTimes(string $dateStr, ?AvailabilityWindow $window, Collection $blocks, Collection $bookings, int $durationMinutes): array
    {
        // A full-day block (start_time is null) voids the entire day.
        if ($blocks->contains(fn (BlockedDate $b) => $b->isFullDay())) {
            return [];
        }

        // No explicit AvailabilityWindow = the photographer hasn't narrowed
        // this date's hours, so the whole day is open by default. Blocks and
        // bookings below are still what actually carve out unavailable time.
        $windowStart = $window
            ? Carbon::parse("{$dateStr} {$window->start_time}")
            : Carbon::parse("{$dateStr} 00:00");
        $windowEnd = $window
            ? Carbon::parse("{$dateStr} {$window->end_time}")
            : Carbon::parse("{$dateStr} 00:00")->addDay();

        $busyRanges = [];

        foreach ($blocks as $block) {
            $busyRanges[] = [
                Carbon::parse("{$dateStr} {$block->start_time}"),
                Carbon::parse("{$dateStr} {$block->end_time}"),
            ];
        }

        foreach ($bookings as $booking) {
            $bookingDateStr = $booking->event_date->format('Y-m-d');
            $bookingStart = Carbon::parse("{$bookingDateStr} {$booking->start_time}");
            // Bookings without a stored end_time are assumed to occupy the
            // same duration being queried for, as a conservative fallback.
            $bookingEnd = $booking->end_time
                ? Carbon::parse("{$bookingDateStr} {$booking->end_time}")
                : $bookingStart->copy()->addMinutes($durationMinutes);
            $busyRanges[] = [$bookingStart, $bookingEnd];
        }

        $slots = [];
        $cursor = $windowStart->copy();

        while ($cursor->copy()->addMinutes($durationMinutes)->lte($windowEnd)) {
            $slotEnd = $cursor->copy()->addMinutes($durationMinutes);

            $overlaps = collect($busyRanges)->contains(
                fn ($range) => $cursor->lt($range[1]) && $slotEnd->gt($range[0])
            );

            if (! $overlaps) {
                $slots[] = $cursor->format('H:i');
            }

            $cursor->addMinutes(self::SLOT_STEP_MINUTES);
        }

        return $slots;
    }
}