<?php

namespace Tests\Feature\Photographer;

use App\Models\PhotographerProfile;
use App\Models\PhotographerPortfolioImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhotographerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_photographer_can_create_their_own_profile(): void
    {
        $user = User::factory()->photographer()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/photographer/profile', [
            'bio' => 'Wedding and portrait photographer.',
            'style' => 'Candid',
        ]);

        $response->assertCreated()->assertJsonPath('success', true);
        $this->assertDatabaseHas('photographer_profiles', ['user_id' => $user->id]);
    }

    public function test_cannot_create_a_second_profile(): void
    {
        $user = User::factory()->photographer()->create();
        PhotographerProfile::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/photographer/profile', [])->assertStatus(422);
    }

    public function test_photographer_can_view_their_own_profile(): void
    {
        $user = User::factory()->photographer()->create();
        PhotographerProfile::factory()->for($user)->complete()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/photographer/profile')
            ->assertOk()
            ->assertJsonPath('data.is_complete', true);
    }

    public function test_photographer_can_update_their_own_profile(): void
    {
        Storage::fake('public');
        $user = User::factory()->photographer()->create();
        PhotographerProfile::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/photographer/profile', [
            'bio' => 'Updated bio',
            'facebook' => 'https://facebook.com/mypage',
            'profile_photo' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

        $response->assertOk()->assertJsonPath('data.bio', 'Updated bio');
        $this->assertDatabaseHas('photographer_profiles', ['user_id' => $user->id, 'bio' => 'Updated bio']);
    }

    public function test_photographer_cannot_modify_another_photographers_profile(): void
    {
        $owner = User::factory()->photographer()->create();
        PhotographerProfile::factory()->for($owner)->create();

        $other = User::factory()->photographer()->create();
        PhotographerProfile::factory()->for($other)->create();

        Sanctum::actingAs($other);

        // Self-service routes always resolve the caller's own profile —
        // there is no route parameter to substitute another user's ID,
        // so cross-account writes are structurally impossible here.
        $response = $this->patchJson('/api/photographer/profile', ['bio' => 'Hijacked']);
        $response->assertOk();
        $this->assertDatabaseHas('photographer_profiles', ['user_id' => $other->id, 'bio' => 'Hijacked']);
        $this->assertDatabaseMissing('photographer_profiles', ['user_id' => $owner->id, 'bio' => 'Hijacked']);
    }

    public function test_client_cannot_create_or_access_photographer_profile_endpoints(): void
    {
        $client = User::factory()->create();
        Sanctum::actingAs($client);

        $this->postJson('/api/photographer/profile', [])->assertStatus(403);
    }
}