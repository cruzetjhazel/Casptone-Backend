<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user): bool
    {
        return $user->isClient();
    }

    public function view(User $user, Review $review): bool
    {
        return $user->id === $review->client_id || $user->id === $review->photographer_id;
    }

    public function reply(User $user, Review $review): bool
    {
        return $user->id === $review->photographer_id && $user->isEligibleForBusinessManagement();
    }

    public function report(User $user, Review $review): bool
    {
        return $user->id === $review->photographer_id && $user->isEligibleForBusinessManagement();
    }

    // Deliberately no update()/delete() for photographers — a review can
    // never be hidden or removed by the professional it's about, only
    // replied to or reported to an admin.
}