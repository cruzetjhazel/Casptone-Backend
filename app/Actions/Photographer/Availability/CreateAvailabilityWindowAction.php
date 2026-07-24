<?php

namespace App\Actions\Photographer\Availability;

use App\Models\AvailabilityWindow;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateAvailabilityWindowAction
{
    public function execute(User $user, array $data): AvailabilityWindow
    {
        $this->assertNoOverlap($user, $data['date'], $data['start_time'], $data['end_time']);

        return AvailabilityWindow::create([
            'user_id' => $user->id,
            'date' => $data['date'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
        ]);
    }

    public function assertNoOverlap(User $user, string $date, string $start, string $end, ?int $ignoreId = null): void
    {
        $overlaps = $user->availabilityWindows()
            ->where('date', $date)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages([
                'start_time' => ['This availability window overlaps an existing one for this date.'],
            ]);
        }
    }
}