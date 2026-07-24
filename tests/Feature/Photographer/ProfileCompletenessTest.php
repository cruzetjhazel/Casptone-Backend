<?php

namespace Tests\Feature\Photographer;

use App\Models\PhotographerPortfolioImage;
use App\Models\PhotographerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileCompletenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_completeness_is_false_when_profile_fields_are_missing(): void
    {
        $user = User::factory()->photographer()->create();
        PhotographerProfile::factory()->for($user)->create(); // incomplete
        PhotographerPortfolioImage::factory()->for($user)->count(6)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/photographer/profile/completeness');

        $response->assertOk()
            ->assertJsonPath('data.profile_complete', false)
            ->assertJsonPath('data.module_3_requirements_met', false);
    }

    public function test_completeness_reflects_portfolio_minimum(): void
    {
        $user = User::factory()->photographer()->create();
        PhotographerProfile::factory()->for($user)->complete()->create();
        PhotographerPortfolioImage::factory()->for($user)->count(3)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/photographer/profile/completeness');

        $response->assertOk()
            ->assertJsonPath('data.portfolio_minimum_met', false)
            ->assertJsonPath('data.active_portfolio_count', 3);
    }

    public function test_module_3_requirements_met_when_profile_and_portfolio_satisfied(): void
    {
        $user = User::factory()->photographer()->create();
        PhotographerProfile::factory()->for($user)->complete()->create();
        PhotographerPortfolioImage::factory()->for($user)->count(6)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/photographer/profile/completeness');

        $response->assertOk()
            ->assertJsonPath('data.profile_complete', true)
            ->assertJsonPath('data.portfolio_minimum_met', true)
            ->assertJsonPath('data.module_3_requirements_met', true);

        // Confirms Module 3 completeness never references packages/availability
        $response->assertJsonMissingPath('data.active_package_count');
        $response->assertJsonMissingPath('data.availability_configured');
    }
}