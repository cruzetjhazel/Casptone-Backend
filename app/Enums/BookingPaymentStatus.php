<?php

namespace App\Enums;

/**
 * The aggregate payment state of a Booking (§8.6). Separate from
 * BookingStatus and, later, any Service Tracker status (§8.10).
 *
 * Failed and Cancelled are included for spec completeness (§8.6 lists
 * five possible values) but are not reachable through the current
 * GCash manual-submission flow, which has no external gateway callback
 * to report a failure — they're a seam for that future integration.
 */
enum BookingPaymentStatus: string
{
    case Pending = 'pending';
    case PartiallyPaid = 'partially_paid';
    case FullyPaid = 'fully_paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}