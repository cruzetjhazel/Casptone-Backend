<?php

namespace App\Http\Controllers\Api\Photographer;

use App\Actions\Photographer\Availability\CreateAvailabilityWindowAction;
use App\Actions\Photographer\Availability\DeleteAvailabilityWindowAction;
use App\Actions\Photographer\Availability\UpdateAvailabilityWindowAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\AvailabilityWindowRequest;
use App\Http\Resources\AvailabilityWindowResource;
use App\Models\AvailabilityWindow;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class AvailabilityWindowController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $this->authorize('viewAny', AvailabilityWindow::class);

        return $this->success(
            AvailabilityWindowResource::collection($request->user()->availabilityWindows()->orderBy('date')->get())
        );
    }

    public function store(AvailabilityWindowRequest $request, CreateAvailabilityWindowAction $action)
    {
        $this->authorize('create', AvailabilityWindow::class);

        $window = $action->execute($request->user(), $request->validated());

        return $this->success(new AvailabilityWindowResource($window), 'Availability window created.', 201);
    }

    public function update(AvailabilityWindowRequest $request, AvailabilityWindow $availabilityWindow, UpdateAvailabilityWindowAction $action)
    {
        $this->authorize('update', $availabilityWindow);

        $window = $action->execute($availabilityWindow, $request->validated());

        return $this->success(new AvailabilityWindowResource($window), 'Availability window updated.');
    }

    public function destroy(AvailabilityWindow $availabilityWindow, DeleteAvailabilityWindowAction $action)
    {
        $this->authorize('delete', $availabilityWindow);

        $action->execute($availabilityWindow);

        return $this->success(null, 'Availability window deleted.');
    }
}