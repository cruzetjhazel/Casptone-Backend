<?php

namespace App\Enums;

enum CancellationDecision: string
{
    case Approved = 'approved';
    case Rejected = 'rejected';
}