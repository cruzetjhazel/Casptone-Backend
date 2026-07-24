<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\PublicPackageResource;
use App\Http\Resources\PublicAddOnResource;

class PhotographerPublicProfileResource extends JsonResource
{
    /**
     * $this = the User model (photographer). Only fields explicitly
     * intended for public display are included — never verification
     * documents, application review metadata, or account internals.
     */
    public function toArray(Request $request): array
    {
        $application = $this->photographerApplication;
        $profile = $this->photographerProfile;

        return [
            'id' => $this->id,
            'photographer_type' => $application?->professional_type?->value,
            'business_name' => $application?->business_name,
            'location' => $application?->location,
            'coverage_area' => $application?->coverage_area,
            'services' => $application?->services,
            'starting_price' => $application?->price_min,
            'style' => $profile?->style,
            'bio' => $profile?->bio,
            'profile_photo_url' => $profile?->profile_photo_path ? Storage::disk('public')->url($profile->profile_photo_path) : null,
            'cover_photo_url' => $profile?->cover_photo_path ? Storage::disk('public')->url($profile->cover_photo_path) : null,
            'social_links' => [
                'facebook' => $profile?->facebook,
                'instagram' => $profile?->instagram,
                'website' => $profile?->website,
            ],
            'portfolio' => PortfolioImageResource::collection(
                $this->portfolioImages->where('status', \App\Enums\PortfolioImageStatus::Active)
            ),
            'packages' => PublicPackageResource::collection(
                $this->packages->where('status', \App\Enums\PackageStatus::Published)
            ),
            'add_ons' => PublicAddOnResource::collection(
                $this->addOns->where('status', \App\Enums\AddOnStatus::Active)
            ),
        ];
    }
}