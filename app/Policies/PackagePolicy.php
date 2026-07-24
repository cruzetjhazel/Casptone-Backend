<?php

namespace App\Policies;

use App\Models\Package;
use App\Models\User;

class PackagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isEligibleForBusinessManagement();
    }

    public function view(User $user, Package $package): bool
    {
        return $user->id === $package->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isEligibleForBusinessManagement();
    }

    public function update(User $user, Package $package): bool
    {
        return $user->id === $package->user_id;
    }

    public function transition(User $user, Package $package): bool
    {
        return $user->id === $package->user_id;
    }

    public function delete(User $user, Package $package): bool
    {
        return $user->id === $package->user_id;
    }
}