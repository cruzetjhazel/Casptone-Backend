<?php

namespace App\Actions\Photographer\Package;

use App\Models\Package;
use Illuminate\Validation\ValidationException;

class UpdatePackageAction
{
    public function execute(Package $package, array $data): Package
    {
        if (! $package->isEditable()) {
            throw ValidationException::withMessages([
                'status' => ['Archived packages cannot be edited. Restore it first.'],
            ]);
        }

        $package->fill(collect($data)->only([
            'name', 'description', 'included_items', 'price', 'duration_minutes', 'buffer_minutes',
        ])->toArray())->save();

        return $package->fresh();
    }
}