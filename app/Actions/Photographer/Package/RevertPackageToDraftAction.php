<?php

namespace App\Actions\Photographer\Package;

use App\Enums\PackageStatus;
use App\Models\Package;
use Illuminate\Validation\ValidationException;

class RevertPackageToDraftAction
{
    public function execute(Package $package): Package
    {
        if ($package->status !== PackageStatus::Published) {
            throw ValidationException::withMessages([
                'status' => ['Only published packages can be reverted to draft.'],
            ]);
        }

        $package->update(['status' => PackageStatus::Draft]);

        return $package->fresh();
    }
}