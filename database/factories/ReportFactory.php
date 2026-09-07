<?php

namespace Database\Factories;

use App\Enums\ReportRequestedAction;
use App\Enums\ReportSeverity;
use App\Enums\ReportStatus;
use App\Enums\ReportTargetType;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'reporter_id' => User::factory(),
            'target_type' => ReportTargetType::Other->value,
            'reference_id' => null,
            'reason' => 'Unexpected behavior',
            'severity' => ReportSeverity::Low,
            'details' => fake()->paragraph(),
            'requested_action' => ReportRequestedAction::Investigate,
            'status' => ReportStatus::Submitted,
            'attachments' => null,
        ];
    }

    public function resolved(): static
    {
        return $this->state(fn () => [
            'status' => ReportStatus::Resolved,
            'resolved_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'status' => ReportStatus::Closed,
            'resolved_at' => now(),
        ]);
    }

    public function underReview(): static
    {
        return $this->state(fn () => ['status' => ReportStatus::UnderReview]);
    }
}
