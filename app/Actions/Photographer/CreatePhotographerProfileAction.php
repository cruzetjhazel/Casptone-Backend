<?php

namespace App\Actions\Photographer;

use App\Models\PhotographerProfile;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreatePhotographerProfileAction
{
    public function execute(User $user, array $data): PhotographerProfile
    {
        if ($user->photographerProfile()->exists()) {
            throw ValidationException::withMessages([
                'profile' => ['A profile already exists for this account.'],
            ]);
        }

        return PhotographerProfile::create([
            'user_id' => $user->id,
            'bio' => $data['bio'] ?? null,
            'style' => $data['style'] ?? null,
            'facebook' => $data['facebook'] ?? null,
            'instagram' => $data['instagram'] ?? null,
            'website' => $data['website'] ?? null,
        ]);
    }
}