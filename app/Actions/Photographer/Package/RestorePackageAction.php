<?php

namespace App\Actions\Photographer\Package;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Validation\ValidationException;

class RestorePackageAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(Package $package): Package
    {
        if ($package->status !== PackageStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => ['Only archived packages can be restored.'],
            ]);
        }

        $package->update(['status' => PackageStatus::Draft]);
        $fresh = $package->fresh();

        $this->activityLogger->execute(
            causer: $fresh->user,
            subject: $fresh,
            action: 'package.restored',
            description: "Restored package \"{$fresh->name}\" to draft",
        );

        return $fresh;
    }
}