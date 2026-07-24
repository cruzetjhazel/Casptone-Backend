<?php

namespace Database\Factories;

use App\Models\BlockedDate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlockedDateFactory extends Factory
{
    protected $model = BlockedDate::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->photographer(),
            'date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => null,
            'end_time' => null,
            'reason' => null,
        ];
    }

    public function partial(): static
    {
        return $this->state(fn () => [
            'start_time' => '12:00',
            'end_time' => '13:00',
            'reason' => 'Lunch break',
        ]);
    }
}