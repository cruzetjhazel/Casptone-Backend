<?php

namespace App\Policies;

use App\Models\AddOn;
use App\Models\User;

class AddOnPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isEligibleForBusinessManagement();
    }

    public function view(User $user, AddOn $addOn): bool
    {
        return $user->id === $addOn->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isEligibleForBusinessManagement();
    }

    public function update(User $user, AddOn $addOn): bool
    {
        return $user->id === $addOn->user_id;
    }

    public function delete(User $user, AddOn $addOn): bool
    {
        return $user->id === $addOn->user_id;
    }
}