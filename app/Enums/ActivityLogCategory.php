<?php

namespace App\Enums;

enum ActivityLogCategory: string
{
    case Bookings = 'bookings';
    case Payments = 'payments';
    case Packages = 'packages';
    case Clients = 'clients';
}