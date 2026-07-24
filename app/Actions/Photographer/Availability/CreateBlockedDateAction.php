<?php

namespace App\Actions\Photographer\Availability;

use App\Models\BlockedDate;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateBlockedDateAction
{
    public function execute(User $user, array $data): BlockedDate
    {
        $this->assertNoOverlap($user, $data['date'], $data['start_time'] ?? null, $data['end_time'] ?? null);

        return BlockedDate::create([
            'user_id' => $user->id,
            'date' => $data['date'],
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'reason' => $data['reason'] ?? null,
        ]);
    }

    public function assertNoOverlap(User $user, string $date, ?string $start, ?string $end, ?int $ignoreId = null): void
    {
        $existing = $user->blockedDates()
            ->where('date', $date)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->get();

        foreach ($existing as $block) {
            // A full-day block always conflicts with any other block on that date.
            if (is_null($start) || is_null($block->start_time)) {
                throw ValidationException::withMessages([
                    'date' => ['This date already has a conflicting blocked period.'],
                ]);
            }

            if ($start < $block->end_time && $block->start_time < $end) {
                throw ValidationException::withMessages([
                    'start_time' => ['This blocked period overlaps an existing one for this date.'],
                ]);
            }
        }
    }
}