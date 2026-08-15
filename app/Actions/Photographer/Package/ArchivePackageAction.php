<?php

namespace App\Actions\Photographer\Package;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Validation\ValidationException;

class ArchivePackageAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(Package $package): Package
    {
        if ($package->status === PackageStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => ['This package is already archived.'],
            ]);
        }

        $package->update(['status' => PackageStatus::Archived]);
        $fresh = $package->fresh();

        $this->activityLogger->execute(
            causer: $fresh->user,
            subject: $fresh,
            action: 'package.archived',
            description: "Archived package \"{$fresh->name}\"",
        );

        return $fresh;
    }
}