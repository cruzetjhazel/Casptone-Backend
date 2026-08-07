<?php

namespace App\Policies;

use App\Enums\AccountType;
use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Report $report): bool
    {
        return $user->account_type === AccountType::Administrator
            || $report->reporter_id === $user->id;
    }
}