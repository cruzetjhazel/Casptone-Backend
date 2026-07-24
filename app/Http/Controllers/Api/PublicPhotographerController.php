<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhotographerPublicProfileResource;
use App\Models\User;
use App\Traits\ApiResponses;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicPhotographerController extends Controller
{
    use ApiResponses;

    public function show(User $user)
    {
        if (! $user->isPhotographer() || ! $user->isApprovedPhotographer()) {
            throw new NotFoundHttpException('Photographer profile not found.');
        }

        $user->load(['photographerProfile', 'photographerApplication', 'portfolioImages']);

        return $this->success(new PhotographerPublicProfileResource($user));
    }
}