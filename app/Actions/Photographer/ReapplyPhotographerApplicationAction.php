<?php

namespace App\Actions\Photographer;

use App\Enums\PhotographerApplicationStatus;
use App\Models\PhotographerApplication;
use Illuminate\Validation\ValidationException;

class ReapplyPhotographerApplicationAction
{
    public function execute(PhotographerApplication $application): PhotographerApplication
    {
        if ($application->status !== PhotographerApplicationStatus::Rejected || ! $application->can_reapply) {
            throw ValidationException::withMessages([
                'status' => ['This application is not eligible for reapplication.'],
            ]);
        }

        $application->fill([
            'status' => PhotographerApplicationStatus::Draft,
            'rejection_reason' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'submitted_at' => null,
        ])->save();

        return $application->fresh();
    }
}