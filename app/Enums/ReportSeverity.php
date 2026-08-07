<?php

namespace App\Enums;

enum ReportSeverity: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';
}