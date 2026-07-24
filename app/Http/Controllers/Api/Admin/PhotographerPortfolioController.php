<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Photographer\ArchivePortfolioImageAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\PortfolioImageResource;
use App\Models\PhotographerPortfolioImage;
use App\Traits\ApiResponses;

class PhotographerPortfolioController extends Controller
{
    use ApiResponses;

    public function archive(PhotographerPortfolioImage $portfolioImage, ArchivePortfolioImageAction $action)
    {
        $this->authorize('archive', $portfolioImage);

        $image = $action->execute($portfolioImage);

        return $this->success(new PortfolioImageResource($image), 'Image archived by administrator.');
    }
}