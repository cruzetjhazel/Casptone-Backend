<?php

namespace App\Actions\Photographer\AddOn;

use App\Enums\AddOnStatus;
use App\Models\AddOn;
use Illuminate\Validation\ValidationException;

class UpdateAddOnAction
{
    public function execute(AddOn $addOn, array $data): AddOn
    {
        if ($addOn->status === AddOnStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => ['Archived add-ons cannot be edited. Restore it first.'],
            ]);
        }

        $addOn->fill(collect($data)->only(['name', 'description', 'price'])->toArray())->save();

        return $addOn->fresh();
    }
}