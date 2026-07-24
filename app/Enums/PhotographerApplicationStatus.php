<?php

namespace App\Enums;

enum PhotographerApplicationStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case RevisionRequested = 'revision_requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
}