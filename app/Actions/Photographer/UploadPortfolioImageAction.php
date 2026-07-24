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

        return PhotographerPortfolioImage::create([
            'user_id' => $user->id,
            'path' => $path,
            'status' => PortfolioImageStatus::Active,
        ]);
    }
}