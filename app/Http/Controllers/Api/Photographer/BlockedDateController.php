<?php

namespace App\Http\Controllers\Api\Photographer;

use App\Actions\Photographer\Availability\CreateBlockedDateAction;
use App\Actions\Photographer\Availability\DeleteBlockedDateAction;
use App\Actions\Photographer\Availability\UpdateBlockedDateAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\BlockedDateRequest;
use App\Http\Resources\BlockedDateResource;
use App\Models\BlockedDate;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class BlockedDateController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $this->authorize('viewAny', BlockedDate::class);

        return $this->success(
            BlockedDateResource::collection($request->user()->blockedDates()->orderBy('date')->get())
        );
    }

    public function store(BlockedDateRequest $request, CreateBlockedDateAction $action)
    {
        $this->authorize('create', BlockedDate::class);

        $block = $action->execute($request->user(), $request->validated());

        return $this->success(new BlockedDateResource($block), 'Blocked date created.', 201);
    }

    public function update(BlockedDateRequest $request, BlockedDate $blockedDate, UpdateBlockedDateAction $action)
    {
        $this->authorize('update', $blockedDate);

        $block = $action->execute($blockedDate, $request->validated());

        return $this->success(new BlockedDateResource($block), 'Blocked date updated.');
    }

    public function destroy(BlockedDate $blockedDate, DeleteBlockedDateAction $action)
    {
        $this->authorize('delete', $blockedDate);

        $action->execute($blockedDate);

        return $this->success(null, 'Blocked date deleted.');
    }
}