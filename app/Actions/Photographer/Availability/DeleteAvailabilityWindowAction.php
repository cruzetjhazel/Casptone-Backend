<?php

namespace App\Actions\Photographer\Availability;

use App\Models\AvailabilityWindow;

class DeleteAvailabilityWindowAction
{
    public function execute(AvailabilityWindow $window): void
    {
        $window->delete();
    }
}