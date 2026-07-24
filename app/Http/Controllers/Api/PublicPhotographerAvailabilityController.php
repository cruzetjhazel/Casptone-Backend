<?php

namespace App\Http\Controllers\Api;

use App\Enums\PackageStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Photographer\AvailabilityService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicPhotographerAvailabilityController extends Controller
{
    use ApiResponses;

    public function calendar(Request $request, User $user, AvailabilityService $service)
    {
        $this->guardPublicAccess($user);

        $request->validate(['month' => ['required', 'date_format:Y-m'], 'package_id' => ['required', 'integer']]);

        $package = $this->resolvePackage($user, $request->integer('package_id'));

        $start = "{$request->query('month')}-01";
        $end = date('Y-m-t', strtotime($start));

        $summary = $service->getMonthSummary($user, $start, $end, $package->duration_minutes + $package->buffer_minutes);

        return $this->success($summary);
    }

    public function slots(Request $request, User $user, AvailabilityService $service)
    {
        $this->guardPublicAccess($user);

        $request->validate(['date' => ['required', 'date_format:Y-m-d'], 'package_id' => ['required', 'integer']]);

        $package = $this->resolvePackage($user, $request->integer('package_id'));

        $slots = $service->getAvailableStartTimes($user, $request->query('date'), $package->duration_minutes + $package->buffer_minutes);

        return $this->success(['date' => $request->query('date'), 'start_times' => $slots]);
    }

    protected function guardPublicAccess(User $user): void
    {
        if (! $user->isPhotographer() || ! $user->isApprovedPhotographer()) {
            throw new NotFoundHttpException('Photographer not found.');
        }
    }

    protected function resolvePackage(User $user, int $packageId)
    {
        $package = $user->packages()->where('status', PackageStatus::Published)->find($packageId);

        abort_if(! $package, 404, 'Package not found for this photographer.');

        return $package;
    }
}