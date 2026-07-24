<?php

namespace Database\Factories;

use App\Models\PhotographerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotographerProfileFactory extends Factory
{
    protected $model = PhotographerProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->photographer(),
        ];
    }

    public function complete(): static
    {
        return $this->state(fn () => [
            'bio' => fake()->paragraph(),
            'style' => 'Candid, Documentary',
            'profile_photo_path' => 'photographers/fake/profile.jpg',
            'cover_photo_path' => 'photographers/fake/cover.jpg',
            'facebook' => 'https://facebook.com/fake',
        ]);
    }
}