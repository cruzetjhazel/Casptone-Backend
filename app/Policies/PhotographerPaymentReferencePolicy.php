<?php

namespace App\Policies;

use App\Models\PhotographerPaymentReference;
use App\Models\User;

class PhotographerPaymentReferencePolicy
{
    public function create(User $user): bool
    {
        return $user->isPhotographer();
    }

    public function invalidate(User $user, PhotographerPaymentReference $reference): bool
    {
        return $user->id === $reference->photographer_id;
    }
}