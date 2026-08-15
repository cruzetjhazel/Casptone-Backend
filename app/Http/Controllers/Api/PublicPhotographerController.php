<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PhotographerPublicProfileResource;
use App\Models\User;
use App\Models\FavoritePhotographer;
use App\Traits\ApiResponses;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicPhotographerController extends Controller
{
    use ApiResponses;

    public function index(\Illuminate\Http\Request $request)
{
    $request->validate(['date' => ['nullable', 'date_format:Y-m-d']]);

    $photographers = User::query()
        ->where('account_type', \App\Enums\AccountType::Photographer)
        ->whereHas('photographerApplication', fn ($q) =>
            $q->where('status', \App\Enums\PhotographerApplicationStatus::Approved)
        )
        ->when($request->filled('date'), function ($query) use ($request) {
            $date = $request->query('date');
            $query->whereDoesntHave('blockedDates', fn ($q) =>
                $q->where('date', $date)->whereNull('start_time')
            );
        })
        ->withCount(['favoritePhotographers as favorites_count', 'bookingsAsPhotographer as bookings_count'])
        ->with(['photographerProfile', 'photographerApplication', 'portfolioImages', 'packages', 'addOns'])
        ->get();

    return $this->success(PhotographerPublicProfileResource::collection($photographers));
}

    public function show(User $user)
    {
        if (! $user->isPhotographer() || ! $user->isApprovedPhotographer()) {
            throw new NotFoundHttpException('Photographer profile not found.');
        }

        $user->load(['photographerProfile', 'photographerApplication', 'portfolioImages', 'packages', 'addOns']);

        return $this->success(new PhotographerPublicProfileResource($user));
    }

    public function featured()
    {
        $photographers = User::query()
            ->where('account_type', \App\Enums\AccountType::Photographer)
            ->whereHas('photographerApplication', fn ($q) =>
                $q->where('status', \App\Enums\PhotographerApplicationStatus::Approved)
            )
            ->with(['photographerProfile', 'photographerApplication', 'portfolioImages', 'packages', 'addOns'])
            ->addSelect(['favorites_count' => FavoritePhotographer::selectRaw('count(*)')
                ->whereColumn('photographer_id', 'users.id')
            ])
            ->orderByDesc('favorites_count')
            ->limit(5)
            ->get();

        return $this->success(PhotographerPublicProfileResource::collection($photographers));
    }
}