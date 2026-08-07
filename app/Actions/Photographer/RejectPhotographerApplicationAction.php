<?php

namespace App\Actions\Photographer;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\PhotographerApplicationStatus;
use App\Models\PhotographerApplication;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RejectPhotographerApplicationAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(PhotographerApplication $application, User $reviewer, string $reason, bool $canReapply = true): PhotographerApplication
    {
        if ($application->status !== PhotographerApplicationStatus::PendingReview) {
            throw ValidationException::withMessages([
                'status' => ['Only applications with status Pending Review can be rejected.'],
            ]);
        }

        $application->fill([
            'status' => PhotographerApplicationStatus::Rejected,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
            'can_reapply' => $canReapply,
        ])->save();

        $fresh = $application->fresh();

        $this->activityLogger->execute(
            causer: $reviewer,
            subject: $fresh,
            action: 'application.rejected',
            description: "Rejected photographer application #{$fresh->id}",
            metadata: ['reason' => $reason, 'can_reapply' => $canReapply],
        );

        return $fresh;
    }
}