<?php

namespace App\Actions\Photographer\Package;

use App\Enums\PackageStatus;
use App\Models\Package;
use App\Models\User;

class CreatePackageAction
{
    public function execute(User $user, array $data): Package
    {
        return Package::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'included_items' => $data['included_items'] ?? null,
            'price' => $data['price'],
            'duration_minutes' => $data['duration_minutes'],
            'buffer_minutes' => $data['buffer_minutes'] ?? 0,
            'status' => PackageStatus::Draft,
        ]);
    }
}