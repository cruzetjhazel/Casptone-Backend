<?php

namespace App\Http\Controllers\Api\Photographer;

use App\Actions\Photographer\CustomPackage\ArchiveCustomPackageComponentAction;
use App\Actions\Photographer\CustomPackage\CreateCustomPackageComponentAction;
use App\Actions\Photographer\CustomPackage\RestoreCustomPackageComponentAction;
use App\Actions\Photographer\CustomPackage\UpdateCustomPackageComponentAction;
use App\Actions\Photographer\CustomPackage\UpdateCustomPackageConfigAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomPackageComponentRequest;
use App\Http\Requests\CustomPackageConfigRequest;
use App\Http\Resources\CustomPackageComponentResource;
use App\Http\Resources\CustomPackageConfigResource;
use App\Models\CustomPackageComponent;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class CustomPackageController extends Controller
{
    use ApiResponses;

    public function showConfig(Request $request)
    {
        $this->authorize('manage', \App\Models\CustomPackageConfig::class);

        $config = $request->user()->customPackageConfig
            ?? new \App\Models\CustomPackageConfig(['enabled' => false, 'base_fee' => null]);

        return $this->success(new CustomPackageConfigResource($config));
    }

    public function updateConfig(CustomPackageConfigRequest $request, UpdateCustomPackageConfigAction $action)
    {
        $this->authorize('manage', \App\Models\CustomPackageConfig::class);

        $config = $action->execute($request->user(), $request->validated());

        return $this->success(new CustomPackageConfigResource($config), 'Custom package configuration updated.');
    }

    public function indexComponents(Request $request)
    {
        $this->authorize('viewAny', CustomPackageComponent::class);

        return $this->success(
            CustomPackageComponentResource::collection($request->user()->customPackageComponents()->latest()->get())
        );
    }

    public function storeComponent(CustomPackageComponentRequest $request, CreateCustomPackageComponentAction $action)
    {
        $this->authorize('create', CustomPackageComponent::class);

        $component = $action->execute($request->user(), $request->validated());

        return $this->success(new CustomPackageComponentResource($component), 'Component created.', 201);
    }

    public function updateComponent(CustomPackageComponentRequest $request, CustomPackageComponent $component, UpdateCustomPackageComponentAction $action)
    {
        $this->authorize('update', $component);

        $component = $action->execute($component, $request->validated());

        return $this->success(new CustomPackageComponentResource($component), 'Component updated.');
    }

    public function archiveComponent(CustomPackageComponent $component, ArchiveCustomPackageComponentAction $action)
    {
        $this->authorize('archive', $component);

        return $this->success(new CustomPackageComponentResource($action->execute($component)), 'Component archived.');
    }

    public function restoreComponent(CustomPackageComponent $component, RestoreCustomPackageComponentAction $action)
    {
        $this->authorize('restore', $component);

        return $this->success(new CustomPackageComponentResource($action->execute($component)), 'Component restored.');
    }
}