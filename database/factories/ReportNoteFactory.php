<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\ReportNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportNoteFactory extends Factory
{
    protected $model = ReportNote::class;

    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'admin_id' => User::factory()->administrator(),
            'note' => fake()->sentence(),
        ];
    }
}
