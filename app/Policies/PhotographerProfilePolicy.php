<?php

namespace App\Policies;

use App\Models\PhotographerProfile;
use App\Models\User;

class PhotographerProfilePolicy
{
    public function create(User $user): bool
    {
        return $user->isPhotographer();
    }

    public function view(User $user, PhotographerProfile $profile): bool
    {
        return $user->isAdministrator() || $user->id === $profile->user_id;
    }

    public function update(User $user, PhotographerProfile $profile): bool
    {
        return $user->isAdministrator() || $user->id === $profile->user_id;
    }
}