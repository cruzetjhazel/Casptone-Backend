<?php

namespace App\Actions\Photographer\Package;

use App\Actions\ActivityLog\LogActivityAction;
use App\Models\Package;
use Illuminate\Validation\ValidationException;

class UpdatePackageAction
{
    public function __construct(protected LogActivityAction $activityLogger)
    {
    }

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

        $fresh = $package->fresh();

        $this->activityLogger->execute(
            causer: $fresh->user,
            subject: $fresh,
            action: 'package.updated',
            description: "Updated package \"{$fresh->name}\"",
        );

        return $fresh;
    }
}