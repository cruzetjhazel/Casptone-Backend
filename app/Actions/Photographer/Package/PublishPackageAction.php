<?php

namespace App\Actions\Photographer\Package;

use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Validation\ValidationException;

class PublishPackageAction
{
    public function execute(Package $package): Package
    {
        if ($package->status !== PackageStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => ['Only draft packages can be published.'],
            ]);
        }

        $package->update(['status' => PackageStatus::Published]);

        return $package->fresh();
    }
}