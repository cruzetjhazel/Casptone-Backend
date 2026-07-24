<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $user->id === $payment->client_id || $user->id === $payment->photographer_id;
    }

    public function verify(User $user, Payment $payment): bool
    {
        return $user->id === $payment->photographer_id;
    }

    public function reject(User $user, Payment $payment): bool
    {
        return $user->id === $payment->photographer_id;
    }
}