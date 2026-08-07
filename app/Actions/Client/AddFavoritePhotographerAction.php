<?php

namespace App\Actions\Client;

use App\Actions\ActivityLog\LogActivityAction;
use App\Models\FavoritePhotographer;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AddFavoritePhotographerAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(User $client, User $photographer): FavoritePhotographer
    {
        if (! $photographer->isPhotographer()) {
            throw ValidationException::withMessages([
                'photographer_id' => ['This account is not a Photographer.'],
            ]);
        }

        $exists = FavoritePhotographer::where('client_id', $client->id)
            ->where('photographer_id', $photographer->id)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'photographer_id' => ['You have already favorited this photographer.'],
            ]);
        }

        $favorite = FavoritePhotographer::create([
            'client_id' => $client->id,
            'photographer_id' => $photographer->id,
        ]);

        $this->activityLogger->execute(
            causer: $client,
            subject: $favorite,
            action: 'favorite.added',
            description: "Favorited photographer #{$photographer->id}",
        );

        return $favorite;
    }
}