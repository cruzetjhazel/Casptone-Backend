<?php

namespace App\Actions\Client;

use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateClientProfileAction
{
    public function execute(User $user, array $data, ?UploadedFile $photo = null): ClientProfile
    {
        if (array_key_exists('name', $data) || array_key_exists('phone_number', $data)) {
            $user->fill(collect($data)->only(['name', 'phone_number'])->toArray())->save();
        }

        $profile = $user->clientProfile ?? new ClientProfile(['user_id' => $user->id]);

        $profile->fill(collect($data)->only(['birthday', 'gender', 'address'])->toArray());

        if ($photo) {
            if ($profile->profile_photo_path && Storage::disk('public')->exists($profile->profile_photo_path)) {
                Storage::disk('public')->delete($profile->profile_photo_path);
            }
            $profile->profile_photo_path = $photo->store("clients/{$user->id}/profile", 'public');
        }

        $profile->save();

        return $profile->fresh();
    }
}