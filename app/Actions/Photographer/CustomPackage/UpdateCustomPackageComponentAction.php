<?php

namespace App\Actions\Photographer\CustomPackage;

use App\Models\CustomPackageComponent;

class UpdateCustomPackageComponentAction
{
    public function execute(CustomPackageComponent $component, array $data): CustomPackageComponent
    {
        $component->fill(collect($data)->only(['type', 'label', 'price_addition'])->toArray())->save();

        return $component->fresh();
    }
}