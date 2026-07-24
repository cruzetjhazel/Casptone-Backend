<?php

namespace App\Actions\Photographer;

use App\Enums\PhotographerApplicationStatus;
use App\Models\PhotographerApplication;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RequestPhotographerApplicationRevisionAction
{
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

        return $application->fresh();
    }
}