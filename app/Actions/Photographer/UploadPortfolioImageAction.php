<?php

namespace App\Actions\Photographer;

use App\Enums\PortfolioImageStatus;
use App\Models\PhotographerPortfolioImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class UploadPortfolioImageAction
{
    public const MAX_ACTIVE = 12;

    public function execute(User $user, UploadedFile $file): PhotographerPortfolioImage
    {
        if ($user->activePortfolioImageCount() >= self::MAX_ACTIVE) {
            throw ValidationException::withMessages([
                'image' => ['You already have the maximum of '.self::MAX_ACTIVE.' active portfolio images.'],
            ]);
        }

        $path = $file->store("portfolio/{$user->id}", 'public');

        // Append to the end of the photographer's existing order.
        $nextPosition = $user->portfolioImages()->max('sort_order');
        $nextPosition = $nextPosition === null ? 0 : $nextPosition + 1;

        return PhotographerPortfolioImage::create([
            'user_id' => $user->id,
            'path' => $path,
            'status' => PortfolioImageStatus::Active,
            'sort_order' => $nextPosition,
        ]);
    }
}