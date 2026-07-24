<?php

namespace App\Policies;

use App\Models\PhotographerPortfolioImage;
use App\Models\User;

class PhotographerPortfolioImagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isPhotographer();
    }

    public function view(User $user, PhotographerPortfolioImage $image): bool
    {
        return $user->isAdministrator() || $user->id === $image->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isPhotographer();
    }

    public function archive(User $user, PhotographerPortfolioImage $image): bool
    {
        // Owner manages their own portfolio; Administrator may moderate.
        return $user->isAdministrator() || $user->id === $image->user_id;
    }

    public function restore(User $user, PhotographerPortfolioImage $image): bool
    {
        return $user->id === $image->user_id;
    }

    public function delete(User $user, PhotographerPortfolioImage $image): bool
    {
        return $user->id === $image->user_id;
    }
}