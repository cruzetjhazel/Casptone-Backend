<?php

namespace App\Enums;

enum CustomPackageComponentType: string
{
    case FlatOption = 'flat_option';
    case PhotoCountTier = 'photo_count_tier';
    case DeliveryDurationTier = 'delivery_duration_tier';
}