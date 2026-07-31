<?php

namespace App\Enums;

enum ServiceTrackerStatus: string
{
    case Upcoming = 'upcoming';
    case EventDay = 'event_day';
    case InProgress = 'in_progress';
    case PhotoEditing = 'photo_editing';
    case ReadyForRelease = 'ready_for_release';
    case Completed = 'completed';

    /** Display order — used by the frontend to render the tracker steps in sequence. */
    public static function ordered(): array
    {
        return [
            self::Upcoming,
            self::EventDay,
            self::InProgress,
            self::PhotoEditing,
            self::ReadyForRelease,
            self::Completed,
        ];
    }
}