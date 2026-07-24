<?php

namespace App\Actions\Photographer;

use App\Enums\PortfolioImageStatus;
use App\Models\PhotographerPortfolioImage;
use Illuminate\Validation\ValidationException;

class ArchivePortfolioImageAction
{
    public const MIN_ACTIVE = 6;

    public function execute(PhotographerPortfolioImage $image): PhotographerPortfolioImage
    {
        if ($image->status !== PortfolioImageStatus::Active) {
            throw ValidationException::withMessages([
                'status' => ['Only active images can be archived.'],
            ]);
        }

        $remaining = $image->user->activePortfolioImageCount() - 1;

        if ($remaining < self::MIN_ACTIVE) {
            throw ValidationException::withMessages([
                'status' => ['Archiving this image would drop your active portfolio below the required minimum of '.self::MIN_ACTIVE.'.'],
            ]);
        }

        $image->update(['status' => PortfolioImageStatus::Archived]);

        return $image->fresh();
    }
}