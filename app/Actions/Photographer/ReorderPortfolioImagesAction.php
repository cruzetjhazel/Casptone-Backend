<?php

namespace App\Actions\Photographer;

use App\Enums\PortfolioImageStatus;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ReorderPortfolioImagesAction
{
    /**
     * @param  array<int, int>  $orderedIds  Portfolio image IDs in the desired display order.
     */
    public function execute(User $user, array $orderedIds): void
    {
        $activeIds = $user->portfolioImages()
            ->where('status', PortfolioImageStatus::Active)
            ->pluck('id')
            ->all();

        $expected = $activeIds;
        sort($expected);
        $given = $orderedIds;
        sort($given);

        if ($expected !== $given) {
            throw ValidationException::withMessages([
                'order' => ['The submitted order must include exactly your active portfolio images, each once.'],
            ]);
        }

        foreach (array_values($orderedIds) as $index => $imageId) {
            $user->portfolioImages()
                ->where('id', $imageId)
                ->update(['sort_order' => $index]);
        }
    }
}