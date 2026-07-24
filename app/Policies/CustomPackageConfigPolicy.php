<?php

namespace App\Policies;

use App\Models\CustomPackageConfig;
use App\Models\User;

class CustomPackageConfigPolicy
{
    public function view(User $user, CustomPackageConfig $config): bool
    {
        return $user->id === $config->user_id;
    }

    public function update(User $user, CustomPackageConfig $config): bool
    {
        return $user->id === $config->user_id;
    }

    public function manage(User $user): bool
    {
        return $user->isEligibleForBusinessManagement();
    }
}