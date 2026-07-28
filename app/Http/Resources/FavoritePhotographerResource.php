<?php

namespace App\Http\Resources;

use App\Enums\AccountStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class FavoritePhotographerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $photographer = $this->photographer;
        $application = $photographer->photographerApplication;
        $profile = $photographer->photographerProfile;

        $isAvailable = $photographer->isApprovedPhotographer()
            && $photographer->account_status === AccountStatus::Active;

        return [
            'id' => $this->id,
            'photographer_id' => $photographer->id,
            'business_name' => $application?->business_name,
            'style' => $profile?->style,
            'profile_photo_url' => $profile?->profile_photo_path ? Storage::disk('public')->url($profile->profile_photo_path) : null,
            'is_available' => $isAvailable,
            'favorited_at' => $this->created_at,
        ];
    }
}