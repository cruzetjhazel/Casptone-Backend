<?php

namespace App\Enums;

enum ReportRequestedAction: string
{
    case Investigate = 'investigate';
    case Refund = 'refund';
    case Cancel = 'cancel';
    case Warn = 'warn';
    case RemoveReview = 'remove_review';
    case Other = 'other';
}