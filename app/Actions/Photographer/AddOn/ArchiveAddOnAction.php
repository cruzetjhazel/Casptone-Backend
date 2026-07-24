<?php

namespace App\Actions\Photographer\AddOn;

use App\Enums\AddOnStatus;
use App\Models\AddOn;
use Illuminate\Validation\ValidationException;

class ArchiveAddOnAction
{
    public function execute(AddOn $addOn): AddOn
    {
        if ($addOn->status === AddOnStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => ['This add-on is already archived.'],
            ]);
        }

        $addOn->update(['status' => AddOnStatus::Archived]);

        return $addOn->fresh();
    }
}