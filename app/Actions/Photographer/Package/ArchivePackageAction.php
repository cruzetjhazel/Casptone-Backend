<?php

namespace App\Actions\Photographer\Package;

use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Validation\ValidationException;

class ArchivePackageAction
{
    public function execute(Package $package): Package
    {
        if ($package->status === PackageStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => ['This package is already archived.'],
            ]);
        }

        $package->update(['status' => PackageStatus::Archived]);

        return $package->fresh();
    }
}