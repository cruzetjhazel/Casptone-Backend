<?php

namespace App\Enums;

enum ReportTargetType: string
{
    case Client = 'client';
    case Studio = 'studio';
    case Booking = 'booking';
    case Payment = 'payment';
    case Bug = 'bug';
    case Other = 'other';
}