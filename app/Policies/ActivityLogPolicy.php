<?php

namespace App\Policies;

use App\Enums\AccountType;
use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        // Every authenticated account type has *some* view of activity logs:
        // clients/photographers see their own scope, admins see everything.
        return true;
    }

    public function view(User $user, ActivityLog $activityLog): bool
    {
        if ($user->account_type === AccountType::Administrator) {
            return true;
        }

        return $activityLog->causer_id === $user->id
            || ($activityLog->subject_type === User::class && $activityLog->subject_id === $user->id);
    }

    // Deliberately no delete() ability: spec rule — Activity Logs cannot be
    // deleted by Photographers or Clients. Admin deletion is out of scope
    // unless/until the spec calls for it.
}