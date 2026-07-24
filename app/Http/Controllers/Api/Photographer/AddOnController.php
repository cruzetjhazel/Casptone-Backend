<?php

namespace App\Http\Controllers\Api\Photographer;

use App\Actions\Photographer\AddOn\ArchiveAddOnAction;
use App\Actions\Photographer\AddOn\CreateAddOnAction;
use App\Actions\Photographer\AddOn\DeleteAddOnAction;
use App\Actions\Photographer\AddOn\RestoreAddOnAction;
use App\Actions\Photographer\AddOn\UpdateAddOnAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\AddOnRequest;
use App\Http\Resources\AddOnResource;
use App\Models\AddOn;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class AddOnController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $this->authorize('viewAny', AddOn::class);

        return $this->success(AddOnResource::collection($request->user()->addOns()->latest()->get()));
    }

    public function store(AddOnRequest $request, CreateAddOnAction $action)
    {
        $this->authorize('create', AddOn::class);

        $addOn = $action->execute($request->user(), $request->validated());

        return $this->success(new AddOnResource($addOn), 'Add-on created.', 201);
    }

    public function show(AddOn $addOn)
    {
        $this->authorize('view', $addOn);

        return $this->success(new AddOnResource($addOn));
    }

    public function update(AddOnRequest $request, AddOn $addOn, UpdateAddOnAction $action)
    {
        $this->authorize('update', $addOn);

        $addOn = $action->execute($addOn, $request->validated());

        return $this->success(new AddOnResource($addOn), 'Add-on updated.');
    }

    public function archive(AddOn $addOn, ArchiveAddOnAction $action)
    {
        $this->authorize('update', $addOn);

        return $this->success(new AddOnResource($action->execute($addOn)), 'Add-on archived.');
    }

    public function restore(AddOn $addOn, RestoreAddOnAction $action)
    {
        $this->authorize('update', $addOn);

        return $this->success(new AddOnResource($action->execute($addOn)), 'Add-on restored.');
    }

    public function destroy(AddOn $addOn, DeleteAddOnAction $action)
    {
        $this->authorize('delete', $addOn);

        $action->execute($addOn);

        return $this->success(null, 'Add-on permanently deleted.');
    }
}