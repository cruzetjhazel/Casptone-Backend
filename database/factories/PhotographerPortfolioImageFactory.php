<?php

namespace Database\Factories;

use App\Enums\PortfolioImageStatus;
use App\Models\PhotographerPortfolioImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotographerPortfolioImageFactory extends Factory
{
    protected $model = PhotographerPortfolioImage::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->photographer(),
            'path' => 'portfolio/fake/'.fake()->uuid().'.jpg',
            'status' => PortfolioImageStatus::Active,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => PortfolioImageStatus::Archived]);
    }
}