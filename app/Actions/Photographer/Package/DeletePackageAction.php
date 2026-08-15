<?php

namespace App\Actions\Photographer\Package;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Validation\ValidationException;

class DeletePackageAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(Package $package): void
    {
        if ($package->status !== PackageStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => ['Only archived packages are eligible for permanent deletion.'],
            ]);
        }

        $name = $package->name;
        $user = $package->user;

        $package->delete();

        $this->activityLogger->execute(
            causer: $user,
            subject: null,
            action: 'package.deleted',
            description: "Permanently deleted package \"{$name}\"",
        );
    }
}