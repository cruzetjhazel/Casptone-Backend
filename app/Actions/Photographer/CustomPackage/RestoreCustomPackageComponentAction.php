<?php

namespace App\Actions\Photographer\CustomPackage;

use App\Enums\AddOnStatus;
use App\Models\CustomPackageComponent;
use Illuminate\Validation\ValidationException;

class RestoreCustomPackageComponentAction
{
    public function execute(CustomPackageComponent $component): CustomPackageComponent
    {
        if ($component->status !== AddOnStatus::Archived) {
            throw ValidationException::withMessages(['status' => ['Only archived components can be restored.']]);
        }

        $component->update(['status' => AddOnStatus::Active]);

        return $component->fresh();
    }
}