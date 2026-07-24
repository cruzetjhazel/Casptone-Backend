<?php

namespace App\Actions\Photographer\AddOn;

use App\Enums\AddOnStatus;
use App\Models\AddOn;
use Illuminate\Validation\ValidationException;

class DeleteAddOnAction
{
    public function execute(AddOn $addOn): void
    {
        if ($addOn->status !== AddOnStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => ['Only archived add-ons are eligible for permanent deletion.'],
            ]);
        }

        $addOn->delete();
    }
}