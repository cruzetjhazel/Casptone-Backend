<?php

namespace App\Actions\Photographer;

use App\Enums\PortfolioImageStatus;
use App\Models\PhotographerPortfolioImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeletePortfolioImageAction
{
    public function execute(PhotographerPortfolioImage $image): void
    {
        if ($image->status !== PortfolioImageStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => ['Only archived images are eligible for permanent deletion.'],
            ]);
        }

        if (Storage::disk('public')->exists($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        $image->delete();
    }
}