<?php

namespace App\Actions\Client;

use App\Actions\ActivityLog\LogActivityAction;
use App\Models\FavoritePhotographer;

class RemoveFavoritePhotographerAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(FavoritePhotographer $favorite): void
    {
        $this->activityLogger->execute(
            causer: $favorite->client,
            subject: null,
            action: 'favorite.removed',
            description: "Removed photographer #{$favorite->photographer_id} from favorites",
        );

        $favorite->delete();
    }
}