<?php

namespace App\Actions\Photographer\CustomPackage;

use App\Models\CustomPackageComponent;

class UpdateCustomPackageComponentAction
{
    public function execute(CustomPackageComponent $component, array $data): CustomPackageComponent
    {
        $component->fill(
            collect($data)->only(['type', 'tier_name', 'label', 'price_addition', 'duration_minutes'])->toArray()
        )->save();

        return $component->fresh();
    }
}