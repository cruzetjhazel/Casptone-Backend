<?php

namespace App\Services\Photographer;

use App\Models\User;

class ProfileCompletenessService
{
    /**
     * Evaluates only the Module 3 prerequisites (profile + portfolio).
     * Package and availability requirements from Chapter 5.7 are NOT
     * evaluated here — later modules extend this checklist.
     */
    public function evaluate(User $user): array
    {
        $profile = $user->photographerProfile;
        $activeCount = $user->activePortfolioImageCount();

        $profileComplete = (bool) ($profile && $profile->isComplete());
        $portfolioMinimumMet = $activeCount >= \App\Actions\Photographer\ArchivePortfolioImageAction::MIN_ACTIVE;

        return [
            'profile_complete' => $profileComplete,
            'active_portfolio_count' => $activeCount,
            'portfolio_minimum_met' => $portfolioMinimumMet,
            'portfolio_minimum_required' => \App\Actions\Photographer\ArchivePortfolioImageAction::MIN_ACTIVE,
            'portfolio_maximum_allowed' => \App\Actions\Photographer\UploadPortfolioImageAction::MAX_ACTIVE,
            'module_3_requirements_met' => $profileComplete && $portfolioMinimumMet,
        ];
    }
}