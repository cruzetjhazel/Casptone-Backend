<?php

namespace Database\Factories;

use App\Models\PhotographerPaymentConfig;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhotographerPaymentConfigFactory extends Factory
{
    protected $model = PhotographerPaymentConfig::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->photographer(),
            'gcash_account_name' => $this->faker->name(),
            'gcash_account_number' => '09'.$this->faker->numerify('#########'),
            'gcash_qr_path' => 'gcash-qr/fake-qr.jpg',
        ];
    }
}