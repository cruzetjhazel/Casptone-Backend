<?php

namespace App\Actions\Photographer\CustomPackage;

use App\Models\CustomPackageConfig;
use App\Models\User;

class UpdateCustomPackageConfigAction
{
    public function execute(User $user, array $data): CustomPackageConfig
    {
        return CustomPackageConfig::updateOrCreate(
            ['user_id' => $user->id],
            [
                'enabled' => $data['enabled'],
                'base_fee' => $data['base_fee'] ?? null,
            ]
        );
    }
}