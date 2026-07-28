<?php

namespace App\Actions\Client;

use App\Models\FavoritePhotographer;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AddFavoritePhotographerAction
{
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

        return FavoritePhotographer::create([
            'client_id' => $client->id,
            'photographer_id' => $photographer->id,
        ]);
    }
}