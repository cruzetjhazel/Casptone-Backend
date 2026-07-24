<?php

namespace App\Enums;

/**
 * Lifecycle of a Professional-side payment reference (§9.4). "Matched" is
 * defined in the spec but not reachable in practice here — a successful
 * match and being marked Used happen atomically in the same request, per
 * §9.4: "Once a reference is successfully matched... it is marked as Used."
 */
enum PhotographerPaymentReferenceStatus: string
{
    case Available = 'available';
    case Matched = 'matched';
    case Used = 'used';
    case Invalidated = 'invalidated';
}