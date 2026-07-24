<?php

namespace App\Http\Controllers\Api\Photographer;

use App\Actions\Photographer\Package\ArchivePackageAction;
use App\Actions\Photographer\Package\CreatePackageAction;
use App\Actions\Photographer\Package\DeletePackageAction;
use App\Actions\Photographer\Package\PublishPackageAction;
use App\Actions\Photographer\Package\RestorePackageAction;
use App\Actions\Photographer\Package\RevertPackageToDraftAction;
use App\Actions\Photographer\Package\UpdatePackageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\PackageRequest;
use App\Http\Resources\PackageResource;
use App\Models\Package;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Package::class);

        return $this->success(PackageResource::collection($request->user()->packages()->latest()->get()));
    }

    public function store(PackageRequest $request, CreatePackageAction $action)
    {
        $this->authorize('create', Package::class);

        $package = $action->execute($request->user(), $request->validated());

        return $this->success(new PackageResource($package), 'Package created.', 201);
    }

    public function show(Package $package)
    {
        $this->authorize('view', $package);

        return $this->success(new PackageResource($package));
    }

    public function update(PackageRequest $request, Package $package, UpdatePackageAction $action)
    {
        $this->authorize('update', $package);

        $package = $action->execute($package, $request->validated());

        return $this->success(new PackageResource($package), 'Package updated.');
    }

    public function publish(Package $package, PublishPackageAction $action)
    {
        $this->authorize('transition', $package);

        return $this->success(new PackageResource($action->execute($package)), 'Package published.');
    }

    public function revertToDraft(Package $package, RevertPackageToDraftAction $action)
    {
        $this->authorize('transition', $package);

        return $this->success(new PackageResource($action->execute($package)), 'Package reverted to draft.');
    }

    public function archive(Package $package, ArchivePackageAction $action)
    {
        $this->authorize('transition', $package);

        return $this->success(new PackageResource($action->execute($package)), 'Package archived.');
    }

    public function restore(Package $package, RestorePackageAction $action)
    {
        $this->authorize('transition', $package);

        return $this->success(new PackageResource($action->execute($package)), 'Package restored to draft.');
    }

    public function destroy(Package $package, DeletePackageAction $action)
    {
        $this->authorize('delete', $package);

        $action->execute($package);

        return $this->success(null, 'Package permanently deleted.');
    }
}