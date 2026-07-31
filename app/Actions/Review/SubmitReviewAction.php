<?php

namespace App\Actions\Review;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Validation\ValidationException;

class SubmitReviewAction
{
    public function execute(Booking $booking, array $data): Review
    {
        if ($booking->status !== BookingStatus::Completed) {
            throw ValidationException::withMessages([
                'booking_id' => ['You can only review a completed booking.'],
            ]);
        }

        if ($booking->review()->exists()) {
            throw ValidationException::withMessages([
                'booking_id' => ['You have already submitted a review for this booking.'],
            ]);
        }

        return Review::create([
            'booking_id' => $booking->id,
            'client_id' => $booking->client_id,
            'photographer_id' => $booking->photographer_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);
    }
}