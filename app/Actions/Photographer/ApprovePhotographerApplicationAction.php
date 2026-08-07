<?php

namespace App\Actions\Photographer;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\PhotographerApplicationStatus;
use App\Models\PhotographerApplication;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ApprovePhotographerApplicationAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(PhotographerApplication $application, User $reviewer): PhotographerApplication
    {
        if ($application->status !== PhotographerApplicationStatus::PendingReview) {
            throw ValidationException::withMessages([
                'status' => ['Only applications with status Pending Review can be approved.'],
            ]);
        }

        $application->fill([
            'status' => PhotographerApplicationStatus::Approved,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
            'revision_notes' => null,
        ])->save();

        $fresh = $application->fresh();

        $this->activityLogger->execute(
            causer: $reviewer,
            subject: $fresh,
            action: 'application.approved',
            description: "Approved photographer application #{$fresh->id}",
        );

        return $fresh;
    }
}