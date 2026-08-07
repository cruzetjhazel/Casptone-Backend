<?php

namespace App\Actions\Client;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeactivateAccountAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(User $user): User
    {
        if ($user->hasOngoingBookings()) {
            throw ValidationException::withMessages([
                'account' => ['Your account cannot be deactivated while you have ongoing bookings.'],
            ]);
        }

        $user->update(['account_status' => AccountStatus::Deactivated]);

        $fresh = $user->fresh();

        $this->activityLogger->execute(
            causer: $fresh,
            subject: $fresh,
            action: 'account.deactivated',
            description: 'Deactivated their own account',
        );

        return $fresh;
    }
}