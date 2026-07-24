<?php

namespace Tests\Feature;

use App\Models\AddOn;
use App\Models\CustomPackageComponent;
use App\Models\CustomPackageConfig;
use App\Models\Package;
use App\Models\PhotographerApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPackagesAndAddOnsTest extends TestCase
{
    use RefreshDatabase;

    protected function approvedPhotographer(): User
    {
        $user = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($user)->approved()->create();

        return $user;
    }

    public function test_public_active_packages_are_visible(): void
    {
        $user = $this->approvedPhotographer();
        Package::factory()->for($user)->published()->create(['name' => 'Visible Package']);
        Package::factory()->for($user)->create(); // draft
        Package::factory()->for($user)->archived()->create();

        $response = $this->getJson("/api/photographers/{$user->id}/packages");

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Visible Package', $response->json('data.0.name'));
    }

    public function test_public_active_add_ons_are_visible(): void
    {
        $user = $this->approvedPhotographer();
        AddOn::factory()->for($user)->create(['name' => 'Visible Add-on']);
        AddOn::factory()->for($user)->archived()->create();

        $response = $this->getJson("/api/photographers/{$user->id}/add-ons");

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame('Visible Add-on', $response->json('data.0.name'));
    }

    public function test_public_endpoints_return_404_for_unapproved_photographer(): void
    {
        $user = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($user)->pendingReview()->create();
        Package::factory()->for($user)->published()->create();

        $this->getJson("/api/photographers/{$user->id}/packages")->assertStatus(404);
        $this->getJson("/api/photographers/{$user->id}/add-ons")->assertStatus(404);
    }

    public function test_public_package_resource_excludes_internal_fields(): void
    {
        $user = $this->approvedPhotographer();
        Package::factory()->for($user)->published()->create();

        $response = $this->getJson("/api/photographers/{$user->id}/packages");

        $response->assertJsonMissingPath('data.0.status');
        $response->assertJsonMissingPath('data.0.buffer_minutes');
    }

    public function test_public_profile_includes_published_packages_and_active_add_ons(): void
    {
        $user = $this->approvedPhotographer();
        Package::factory()->for($user)->published()->create(['name' => 'Featured Package']);
        AddOn::factory()->for($user)->create(['name' => 'Featured Add-on']);

        $response = $this->getJson("/api/photographers/{$user->id}");

        $response->assertOk()
            ->assertJsonPath('data.packages.0.name', 'Featured Package')
            ->assertJsonPath('data.add_ons.0.name', 'Featured Add-on');
    }

    public function test_custom_package_config_is_never_exposed_publicly(): void
    {
        $user = $this->approvedPhotographer();
        CustomPackageConfig::factory()->for($user)->create();
        CustomPackageComponent::factory()->for($user)->create();

        $response = $this->getJson("/api/photographers/{$user->id}");

        $response->assertOk();
        $response->assertJsonMissingPath('data.custom_package');
        $response->assertJsonMissingPath('data.base_fee');
    }
}