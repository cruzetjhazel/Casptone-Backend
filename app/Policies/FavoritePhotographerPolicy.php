<?php

namespace App\Policies;

use App\Models\FavoritePhotographer;
use App\Models\User;

class FavoritePhotographerPolicy
{
    public function create(User $user): bool
    {
        return $user->isClient();
    }

    public function delete(User $user, FavoritePhotographer $favorite): bool
    {
        return $user->id === $favorite->client_id;
    }
}