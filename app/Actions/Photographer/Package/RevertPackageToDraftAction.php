<?php

namespace App\Actions\Photographer\Package;

use App\Actions\ActivityLog\LogActivityAction;
use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Validation\ValidationException;

class RevertPackageToDraftAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

    public function execute(Package $package): Package
    {
        if ($package->status !== PackageStatus::Published) {
            throw ValidationException::withMessages([
                'status' => ['Only published packages can be reverted to draft.'],
            ]);
        }

        $package->update(['status' => PackageStatus::Draft]);
        $fresh = $package->fresh();

        $this->activityLogger->execute(
            causer: $fresh->user,
            subject: $fresh,
            action: 'package.reverted_to_draft',
            description: "Reverted package \"{$fresh->name}\" to draft",
        );

        return $fresh;
    }
}