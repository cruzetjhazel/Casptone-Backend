<?php

namespace App\Services\Photographer;

use App\Models\User;

class ProfileCompletenessService
{
    /**
     * Full setup checklist: Module 3 (profile + portfolio) plus the
     * Module 4/7 prerequisites (a published package, GCash payment info)
     * that determine whether a photographer is actually bookable.
     */
    public function evaluate(User $user): array
    {
        $profile = $user->photographerProfile;
        $activeCount = $user->activePortfolioImageCount();

        $profileComplete = (bool) ($profile && $profile->isComplete());
        $portfolioMinimumMet = $activeCount >= \App\Actions\Photographer\ArchivePortfolioImageAction::MIN_ACTIVE;

        $hasActivePackage = $user->hasActivePackage();

        $paymentConfig = $user->paymentConfig;
        $gcashConfigured = (bool) ($paymentConfig
            && $paymentConfig->gcash_account_name
            && $paymentConfig->gcash_account_number);

        $module3Met = $profileComplete && $portfolioMinimumMet;

        return [
            'profile_complete' => $profileComplete,
            'active_portfolio_count' => $activeCount,
            'portfolio_minimum_met' => $portfolioMinimumMet,
            'portfolio_minimum_required' => \App\Actions\Photographer\ArchivePortfolioImageAction::MIN_ACTIVE,
            'portfolio_maximum_allowed' => \App\Actions\Photographer\UploadPortfolioImageAction::MAX_ACTIVE,
            'module_3_requirements_met' => $module3Met,
            'has_active_package' => $hasActivePackage,
            'gcash_configured' => $gcashConfigured,
            'fully_bookable' => $module3Met && $hasActivePackage && $gcashConfigured,
        ];
    }
}