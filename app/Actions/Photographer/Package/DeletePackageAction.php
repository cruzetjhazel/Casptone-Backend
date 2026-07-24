<?php

namespace App\Actions\Photographer\Package;

use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Validation\ValidationException;

class DeletePackageAction
{
    public function execute(Package $package): void
    {
        if ($package->status !== PackageStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => ['Only archived packages are eligible for permanent deletion.'],
            ]);
        }

        $package->delete();
    }
}