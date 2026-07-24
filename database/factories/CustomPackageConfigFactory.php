<?php

namespace Database\Factories;

use App\Models\CustomPackageConfig;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomPackageConfigFactory extends Factory
{
    protected $model = CustomPackageConfig::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->photographer(),
            'enabled' => true,
            'base_fee' => 2000,
        ];
    }
}