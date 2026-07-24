<?php

namespace App\Actions\Photographer\AddOn;

use App\Enums\AddOnStatus;
use App\Models\AddOn;
use Illuminate\Validation\ValidationException;

class RestoreAddOnAction
{
    public function execute(AddOn $addOn): AddOn
    {
        if ($addOn->status !== AddOnStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => ['Only archived add-ons can be restored.'],
            ]);
        }

        $addOn->update(['status' => AddOnStatus::Active]);

        return $addOn->fresh();
    }
}