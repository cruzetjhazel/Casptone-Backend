<?php

namespace App\Services\Photographer;

use App\Enums\BookingStatus;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AvailabilityService
{
    protected const SLOT_STEP_MINUTES = 30;

    /**
     * Free sub-intervals for a date, after subtracting blocked periods
     * from the photographer's declared availability windows and any
     * blocking bookings for that date.
     */
    public function getFreeIntervals(User $user, string $date): array
    {
        $windows = $user->availabilityWindows()->where('date', $date)->orderBy('start_time')->get();

        if ($windows->isEmpty()) {
            return [];
        }

        $blocks = $user->blockedDates()->where('date', $date)->get();
        $blockingBookings = $this->getBlockingBookings($user, $date);

        if ($blocks->contains(fn ($block) => $block->isFullDay())) {
            return [];
        }

        $free = [];

        foreach ($windows as $window) {
            $segments = [[$window->start_time, $window->end_time]];

            foreach ($blocks as $block) {
                $segments = $this->subtract($segments, $block->start_time, $block->end_time);
            }

            foreach ($blockingBookings as $booking) {
                $segments = $this->subtract($segments, $booking->start_time, $booking->end_time);
            }

            foreach ($segments as [$start, $end]) {
                $free[] = ['start' => $start, 'end' => $end];
            }
        }

        return $free;
    }

    protected function subtract(array $segments, string $blockStart, string $blockEnd): array
    {
        $result = [];

        foreach ($segments as [$start, $end]) {
            if ($blockEnd <= $start || $blockStart >= $end) {
                $result[] = [$start, $end];
                continue;
            }

            if ($blockStart > $start) {
                $result[] = [$start, $blockStart];
            }

            if ($blockEnd < $end) {
                $result[] = [$blockEnd, $end];
            }
        }

        return $result;
    }

    protected function getBlockingBookings(User $user, string $date)
    {
        return $user->bookingsAsPhotographer()
            ->where('event_date', $date)
            ->whereIn('status', [
                BookingStatus::Pending->value,
                BookingStatus::Accepted->value,
                BookingStatus::Confirmed->value,
            ])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Candidate starting times where start + neededMinutes fits entirely
     * inside a free interval, stepped at SLOT_STEP_MINUTES.
     */
    public function getAvailableStartTimes(User $user, string $date, int $neededMinutes): array
    {
        $slots = [];

        foreach ($this->getFreeIntervals($user, $date) as $interval) {
            $cursor = Carbon::parse($interval['start']);
            $end = Carbon::parse($interval['end']);

            while ($cursor->copy()->addMinutes($neededMinutes)->lte($end)) {
                $slots[] = $cursor->format('H:i');
                $cursor->addMinutes(self::SLOT_STEP_MINUTES);
            }
        }

        sort($slots);

        return array_values(array_unique($slots));
    }

    public function getDateStatus(User $user, string $date, int $neededMinutes): string
    {
        if (! $user->availabilityWindows()->where('date', $date)->exists()) {
            return 'unavailable';
        }

        $slots = $this->getAvailableStartTimes($user, $date, $neededMinutes);

        if (empty($slots)) {
            return 'unavailable';
        }

        $hasBlock = $user->blockedDates()->where('date', $date)->exists();
        $hasBlockingBooking = $this->getBlockingBookings($user, $date)->isNotEmpty();

        return ($hasBlock || $hasBlockingBooking) ? 'partial' : 'available';
    }

    public function getMonthSummary(User $user, string $monthStart, string $monthEnd, int $neededMinutes): array
    {
        $summary = [];

        foreach (CarbonPeriod::create($monthStart, $monthEnd) as $day) {
            $summary[$day->format('Y-m-d')] = $this->getDateStatus($user, $day->format('Y-m-d'), $neededMinutes);
        }

        return $summary;
    }
}