<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case Resolved = 'resolved';
    case Closed = 'closed';
}