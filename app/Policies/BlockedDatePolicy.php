<?php

namespace App\Policies;

use App\Models\BlockedDate;
use App\Models\User;

class BlockedDatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isEligibleForBusinessManagement();
    }

    public function create(User $user): bool
    {
        return $user->isEligibleForBusinessManagement();
    }

    public function update(User $user, BlockedDate $block): bool
    {
        return $user->id === $block->user_id;
    }

    public function delete(User $user, BlockedDate $block): bool
    {
        return $user->id === $block->user_id;
    }
}