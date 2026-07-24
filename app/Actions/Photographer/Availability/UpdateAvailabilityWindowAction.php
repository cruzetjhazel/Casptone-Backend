<?php

namespace App\Actions\Photographer\Availability;

use App\Models\AvailabilityWindow;

class UpdateAvailabilityWindowAction
{
    public function __construct(protected CreateAvailabilityWindowAction $overlapChecker)
    {
    }

    public function execute(AvailabilityWindow $window, array $data): AvailabilityWindow
    {
        $date = $data['date'] ?? $window->date->format('Y-m-d');
        $start = $data['start_time'] ?? $window->start_time;
        $end = $data['end_time'] ?? $window->end_time;

        $this->overlapChecker->assertNoOverlap($window->user, $date, $start, $end, $window->id);

        $window->fill(['date' => $date, 'start_time' => $start, 'end_time' => $end])->save();

        return $window->fresh();
    }
}