<?php

namespace App\Actions\Photographer;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\PhotographerApplicationStatus;
use App\Models\PhotographerApplication;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RequestPhotographerApplicationRevisionAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(PhotographerApplication $application, User $reviewer, string $notes): PhotographerApplication
    {
        if ($application->status !== PhotographerApplicationStatus::PendingReview) {
            throw ValidationException::withMessages([
                'status' => ['Only applications with status Pending Review can have revisions requested.'],
            ]);
        }

        $application->fill([
            'status' => PhotographerApplicationStatus::RevisionRequested,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'revision_notes' => $notes,
        ])->save();

        $fresh = $application->fresh();

        $this->activityLogger->execute(
            causer: $reviewer,
            subject: $fresh,
            action: 'application.revision_requested',
            description: "Requested revision for photographer application #{$fresh->id}",
            metadata: ['notes' => $notes],
        );

        return $fresh;
    }
}