<?php

namespace App\Enums;

enum BookingLocationType: string
{
    case Studio = 'studio';
    case ClientLocation = 'client_location';
    case OutdoorLocation = 'outdoor_location';
    case Other = 'other';
}