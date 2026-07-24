<?php

namespace Database\Factories;

use App\Models\AvailabilityWindow;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvailabilityWindowFactory extends Factory
{
    protected $model = AvailabilityWindow::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->photographer(),
            'date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ];
    }
}