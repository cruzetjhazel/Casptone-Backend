<?php

namespace App\Actions\Photographer\CustomPackage;

use App\Enums\AddOnStatus;
use App\Models\CustomPackageComponent;
use Illuminate\Validation\ValidationException;

class ArchiveCustomPackageComponentAction
{
    public function execute(CustomPackageComponent $component): CustomPackageComponent
    {
        if ($component->status === AddOnStatus::Archived) {
            throw ValidationException::withMessages(['status' => ['This component is already archived.']]);
        }

        $component->update(['status' => AddOnStatus::Archived]);

        return $component->fresh();
    }
}