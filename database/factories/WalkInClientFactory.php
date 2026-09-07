<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WalkInClient;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalkInClientFactory extends Factory
{
    protected $model = WalkInClient::class;

    public function definition(): array
    {
        return [
            'photographer_id' => User::factory()->photographer(),
            'name' => fake()->name(),
            'phone' => fake()->numerify('09#########'),
            'email' => fake()->optional(0.6)->safeEmail(),
            'location' => fake()->city(),
            'source' => fake()->randomElement(['facebook', 'messenger', 'phone_call', 'walk_in', 'referral']),
            'status' => fake()->randomElement(['active', 'inactive', 'archived']),
        ];
    }
}
