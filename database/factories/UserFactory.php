<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => fake()->numerify('09#########'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'account_type' => AccountType::Client,
            'account_status' => AccountStatus::Active,
            'remember_token' => Str::random(10),
        ];
    }

    public function administrator(): static
    {
        return $this->state(fn () => ['account_type' => AccountType::Administrator]);
    }

    public function photographer(): static
    {
        return $this->state(fn () => ['account_type' => AccountType::Photographer]);
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['account_status' => AccountStatus::Suspended]);
    }

    public function deactivated(): static
    {
        return $this->state(fn () => ['account_status' => AccountStatus::Deactivated]);
    }
}