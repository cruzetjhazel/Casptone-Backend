<?php

namespace App\Http\Controllers\Api\Photographer;

use App\Actions\Photographer\ArchivePortfolioImageAction;
use App\Actions\Photographer\DeletePortfolioImageAction;
use App\Actions\Photographer\RestorePortfolioImageAction;
use App\Actions\Photographer\UploadPortfolioImageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadPortfolioImageRequest;
use App\Http\Resources\PortfolioImageResource;
use App\Models\PhotographerPortfolioImage;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $this->authorize('viewAny', PhotographerPortfolioImage::class);

        $images = $request->user()->portfolioImages()->latest()->get();

        return $this->success(PortfolioImageResource::collection($images));
    }

    public function store(UploadPortfolioImageRequest $request, UploadPortfolioImageAction $action)
    {
        $this->authorize('create', PhotographerPortfolioImage::class);

        $image = $action->execute($request->user(), $request->file('image'));

        return $this->success(new PortfolioImageResource($image), 'Image uploaded.', 201);
    }

    public function archive(PhotographerPortfolioImage $portfolioImage, ArchivePortfolioImageAction $action)
    {
        $this->authorize('archive', $portfolioImage);

        $image = $action->execute($portfolioImage);

        return $this->success(new PortfolioImageResource($image), 'Image archived.');
    }

    public function restore(PhotographerPortfolioImage $portfolioImage, RestorePortfolioImageAction $action)
    {
        $this->authorize('restore', $portfolioImage);

        $image = $action->execute($portfolioImage);

        return $this->success(new PortfolioImageResource($image), 'Image restored.');
    }

    public function destroy(PhotographerPortfolioImage $portfolioImage, DeletePortfolioImageAction $action)
    {
        $this->authorize('delete', $portfolioImage);

        $action->execute($portfolioImage);

        return $this->success(null, 'Image permanently deleted.');
    }
}