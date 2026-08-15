<?php

namespace App\Actions\Photographer\Package;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Validation\ValidationException;

class PublishPackageAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(Package $package): Package
    {
        if ($package->status !== PackageStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => ['Only draft packages can be published.'],
            ]);
        }

        $package->update(['status' => PackageStatus::Published]);
        $fresh = $package->fresh();

        $this->activityLogger->execute(
            causer: $fresh->user,
            subject: $fresh,
            action: 'package.published',
            description: "Published package \"{$fresh->name}\"",
        );

        return $fresh;
    }
}