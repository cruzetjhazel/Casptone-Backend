<?php

namespace App\Policies;

use App\Models\AvailabilityWindow;
use App\Models\User;

class AvailabilityWindowPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isEligibleForBusinessManagement();
    }

    public function create(User $user): bool
    {
        return $user->isEligibleForBusinessManagement();
    }

    public function update(User $user, AvailabilityWindow $window): bool
    {
        return $user->id === $window->user_id;
    }

    public function delete(User $user, AvailabilityWindow $window): bool
    {
        return $user->id === $window->user_id;
    }
}