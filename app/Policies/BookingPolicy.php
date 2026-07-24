<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function create(User $user): bool
    {
        return $user->isClient();
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->id === $booking->client_id || $user->id === $booking->photographer_id;
    }

    public function requestCancellation(User $user, Booking $booking): bool
    {
        return $user->id === $booking->client_id;
    }

    public function respond(User $user, Booking $booking): bool
    {
        return $user->id === $booking->photographer_id;
    }

    public function decideCancellation(User $user, Booking $booking): bool
    {
        return $user->id === $booking->photographer_id;
    }
}