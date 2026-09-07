<?php

namespace App\Actions\Photographer\CustomPackage;

use App\Models\CustomPackageConfig;
use App\Models\User;

class UpdateCustomPackageConfigAction
{
    public function execute(User $user, array $data): CustomPackageConfig
    {
        // The studio UI saves enabled/base_fee and buffer_minutes as separate
        // requests (see StudioPackages.tsx's saveBaseFee vs. the buffer field).
        // Falling back to 0 whenever buffer_minutes is omitted would silently
        // wipe out an already-configured buffer on every unrelated save, so we
        // fall back to whatever is already stored instead.
        $existingBuffer = $user->customPackageConfig?->buffer_minutes ?? 0;

        return CustomPackageConfig::updateOrCreate(
            ['user_id' => $user->id],
            [
                'enabled' => $data['enabled'],
                'base_fee' => $data['base_fee'] ?? null,
                'buffer_minutes' => $data['buffer_minutes'] ?? $existingBuffer,
            ]
        );
    }
}