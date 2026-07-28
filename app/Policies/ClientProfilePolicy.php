<?php

namespace App\Policies;

use App\Models\ClientProfile;
use App\Models\User;

class ClientProfilePolicy
{
    public function view(User $user, ClientProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    public function update(User $user, ClientProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }
}