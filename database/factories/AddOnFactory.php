<?php

namespace Database\Factories;

use App\Enums\AddOnStatus;
use App\Models\AddOn;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddOnFactory extends Factory
{
    protected $model = AddOn::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->photographer(),
            'name' => 'Extra Hour Coverage',
            'description' => 'One additional hour of coverage',
            'price' => 1500,
            'status' => AddOnStatus::Active,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => AddOnStatus::Archived]);
    }
}