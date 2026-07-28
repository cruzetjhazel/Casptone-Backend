<?php

namespace App\Http\Controllers\Api\Client;

use App\Actions\Client\AddFavoritePhotographerAction;
use App\Actions\Client\RemoveFavoritePhotographerAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\FavoritePhotographerResource;
use App\Models\FavoritePhotographer;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FavoritePhotographerController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $favorites = $request->user()->favoritePhotographers()
            ->with(['photographer.photographerApplication', 'photographer.photographerProfile'])
            ->latest()
            ->get();

        return $this->success(FavoritePhotographerResource::collection($favorites));
    }

    public function store(Request $request, User $user, AddFavoritePhotographerAction $action)
    {
        $this->authorize('create', FavoritePhotographer::class);

        $favorite = $action->execute($request->user(), $user);
        $favorite->load(['photographer.photographerApplication', 'photographer.photographerProfile']);

        return $this->success(new FavoritePhotographerResource($favorite), 'Photographer added to favorites.', 201);
    }

    public function destroy(Request $request, User $user, RemoveFavoritePhotographerAction $action)
    {
        $favorite = FavoritePhotographer::where('client_id', $request->user()->id)
            ->where('photographer_id', $user->id)
            ->first();

        if (! $favorite) {
            throw new NotFoundHttpException('Favorite not found.');
        }

        $this->authorize('delete', $favorite);

        $action->execute($favorite);

        return $this->success(null, 'Photographer removed from favorites.');
    }
}