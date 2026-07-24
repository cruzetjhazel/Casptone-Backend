<?php

namespace App\Actions\Photographer\CustomPackage;

use App\Enums\AddOnStatus;
use App\Models\CustomPackageComponent;
use App\Models\User;

class CreateCustomPackageComponentAction
{
    public function execute(User $user, array $data): CustomPackageComponent
    {
        return CustomPackageComponent::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'label' => $data['label'],
            'price_addition' => $data['price_addition'],
            'status' => AddOnStatus::Active,
        ]);
    }
}