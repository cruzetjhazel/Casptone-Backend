<?php

namespace App\Actions\Photographer\Package;

use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Validation\ValidationException;

class RestorePackageAction
{
    public function execute(Package $package): Package
    {
        if ($package->status !== PackageStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => ['Only archived packages can be restored.'],
            ]);
        }

        // Restored packages return to Draft — re-publishing is a deliberate,
        // separate action so a package never silently reappears to Clients.
        $package->update(['status' => PackageStatus::Draft]);

        return $package->fresh();
    }
}