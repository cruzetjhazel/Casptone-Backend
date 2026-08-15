<?php

namespace App\Http\Controllers\Api;

use App\Enums\CustomPackageComponentType;
use App\Http\Controllers\Controller;
use App\Http\Resources\PublicCustomPackageComponentResource;
use App\Http\Resources\PublicCustomPackageConfigResource;
use App\Models\User;
use App\Traits\ApiResponses;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicPhotographerCustomPackageController extends Controller
{
    use ApiResponses;

    public function show(User $user)
    {
        if (! $user->isPhotographer() || ! $user->isApprovedPhotographer()) {
            throw new NotFoundHttpException('Photographer not found.');
        }

        $config = $user->customPackageConfig;

        // No config row, or the photographer has it turned off — tell the
        // client there's nothing to build rather than exposing archived/unset data.
        if (! $config || ! $config->enabled) {
            return $this->success([
                'config' => ['enabled' => false, 'base_fee' => null],
                'components' => [],
            ]);
        }

        $components = $user->customPackageComponents()->where('status', 'active')->get();

        return $this->success([
            'config' => new PublicCustomPackageConfigResource($config),
            'components' => PublicCustomPackageComponentResource::collection($components),
        ]);
    }
}