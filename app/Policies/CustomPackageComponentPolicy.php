<?php

namespace App\Policies;

use App\Models\CustomPackageComponent;
use App\Models\User;

class CustomPackageComponentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isEligibleForBusinessManagement();
    }

    public function create(User $user): bool
    {
        return $user->isEligibleForBusinessManagement();
    }

    public function update(User $user, CustomPackageComponent $component): bool
    {
        return $user->id === $component->user_id;
    }

    public function archive(User $user, CustomPackageComponent $component): bool
    {
        return $user->id === $component->user_id;
    }

    public function restore(User $user, CustomPackageComponent $component): bool
    {
        return $user->id === $component->user_id;
    }
}