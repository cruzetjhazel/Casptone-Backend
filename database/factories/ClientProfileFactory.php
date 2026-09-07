<?php

namespace Database\Factories;

use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientProfileFactory extends Factory
{
    protected $model = ClientProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'profile_photo_path' => null,
            'birthday' => fake()->dateTimeBetween('-55 years', '-18 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['female', 'male', 'prefer_not_to_say']),
            'address' => fake()->streetAddress().', '.fake()->city(),
        ];
    }

    public function withPhoto(): static
    {
        return $this->state(fn () => [
            'profile_photo_path' => 'clients/fake/'.fake()->uuid().'.jpg',
        ]);
    }
}
