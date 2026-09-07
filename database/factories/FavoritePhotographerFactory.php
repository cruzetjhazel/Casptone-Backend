<?php

namespace Database\Factories;

use App\Models\FavoritePhotographer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoritePhotographerFactory extends Factory
{
    protected $model = FavoritePhotographer::class;

    public function definition(): array
    {
        return [
            'client_id' => User::factory(),
            'photographer_id' => User::factory()->photographer(),
        ];
    }
}
