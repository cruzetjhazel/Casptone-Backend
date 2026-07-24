<?php

namespace Database\Factories;

use App\Enums\AddOnStatus;
use App\Enums\CustomPackageComponentType;
use App\Models\CustomPackageComponent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomPackageComponentFactory extends Factory
{
    protected $model = CustomPackageComponent::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->photographer(),
            'type' => CustomPackageComponentType::FlatOption,
            'label' => 'RAW Files Add-on',
            'price_addition' => 1500,
            'status' => AddOnStatus::Active,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => AddOnStatus::Archived]);
    }
}