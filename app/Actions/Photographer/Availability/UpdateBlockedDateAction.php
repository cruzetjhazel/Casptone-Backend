<?php

namespace App\Actions\Photographer\Availability;

use App\Models\BlockedDate;

class UpdateBlockedDateAction
{
    public function __construct(protected CreateBlockedDateAction $overlapChecker)
    {
    }

    public function execute(BlockedDate $block, array $data): BlockedDate
    {
        $date = $data['date'] ?? $block->date->format('Y-m-d');
        $start = array_key_exists('start_time', $data) ? $data['start_time'] : $block->start_time;
        $end = array_key_exists('end_time', $data) ? $data['end_time'] : $block->end_time;

        $this->overlapChecker->assertNoOverlap($block->user, $date, $start, $end, $block->id);

        $block->fill([
            'date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'reason' => $data['reason'] ?? $block->reason,
        ])->save();

        return $block->fresh();
    }
}