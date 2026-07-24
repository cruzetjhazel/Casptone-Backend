<?php

namespace App\Enums;

/**
 * Client-side payment submission lifecycle (§9.4).
 */
enum PaymentMatchingStatus: string
{
    case Submitted = 'submitted';
    case PendingMatch = 'pending_match';
    case Matched = 'matched';
    case NotMatched = 'not_matched';
    case ManuallyVerified = 'manually_verified';
    case Rejected = 'rejected';
}