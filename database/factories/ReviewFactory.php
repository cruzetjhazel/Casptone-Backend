<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'client_id' => User::factory(),
            'photographer_id' => User::factory()->photographer(),
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->paragraph(),
        ];
    }

    public function withReply(): static
    {
        return $this->state(fn () => [
            'reply' => 'Thank you so much for trusting us with your event! It was a pleasure working with you.',
            'replied_at' => now(),
        ]);
    }

    public function reported(): static
    {
        return $this->state(fn () => [
            'report_reason' => 'This review contains information unrelated to the actual booking.',
            'reported_at' => now(),
        ]);
    }
}
