<?php

namespace App\Actions\Client;

use App\Models\FavoritePhotographer;

class RemoveFavoritePhotographerAction
{
    public function execute(FavoritePhotographer $favorite): void
    {
        $favorite->delete();
    }
}