<?php

namespace App\Actions\Client;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeactivateAccountAction
{
    public function execute(User $user): User
    {
        if ($user->hasOngoingBookings()) {
            throw ValidationException::withMessages([
                'account' => ['Your account cannot be deactivated while you have ongoing bookings.'],
            ]);
        }

        $user->update(['account_status' => AccountStatus::Deactivated]);

        return $user->fresh();
    }
}