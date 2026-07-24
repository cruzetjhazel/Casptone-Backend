<?php

namespace App\Actions\Photographer;

use App\Enums\PortfolioImageStatus;
use App\Models\PhotographerPortfolioImage;
use Illuminate\Validation\ValidationException;

class RestorePortfolioImageAction
{
    public function execute(PhotographerPortfolioImage $image): PhotographerPortfolioImage
    {
        if ($image->status !== PortfolioImageStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => ['Only archived images can be restored.'],
            ]);
        }

        if ($image->user->activePortfolioImageCount() >= UploadPortfolioImageAction::MAX_ACTIVE) {
            throw ValidationException::withMessages([
                'status' => ['Restoring this image would exceed the maximum of '.UploadPortfolioImageAction::MAX_ACTIVE.' active images.'],
            ]);
        }

        $image->update(['status' => PortfolioImageStatus::Active]);

        return $image->fresh();
    }
}