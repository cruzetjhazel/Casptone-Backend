<?php

namespace App\Actions\Photographer\AddOn;

use App\Enums\AddOnStatus;
use App\Models\AddOn;
use App\Models\User;

class CreateAddOnAction
{
    public function execute(User $user, array $data): AddOn
    {
        return AddOn::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'status' => AddOnStatus::Active,
        ]);
    }
}