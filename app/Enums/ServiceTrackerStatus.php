<?php

namespace App\Enums;

enum ServiceTrackerStatus: string
{
    case EventDay = 'event_day';
    case Editing = 'editing';
    case Delivered = 'delivered';

    public static function ordered(): array
    {
        return [self::EventDay, self::Editing, self::Delivered];
    }
}