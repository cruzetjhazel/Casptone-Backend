<?php

namespace App\Enums;

enum PaymentType: string
{
    case Online = 'online';
    case Onsite = 'onsite';
}