<?php

namespace Tests\Feature\Client;

use App\Models\FavoritePhotographer;
use App\Models\PhotographerApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FavoritePhotographerTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_favorite_a_photographer(): void
    {
        $photographer = User::factory()->photographer()->create();
        $client = User::factory()->create();
        Sanctum::actingAs($client);

        $this->postJson("/api/client/favorites/{$photographer->id}")->assertCreated();

        $this->assertDatabaseHas('favorite_photographers', ['client_id' => $client->id, 'photographer_id' => $photographer->id]);
    }

    public function test_cannot_favorite_the_same_photographer_twice(): void
    {
        $photographer = User::factory()->photographer()->create();
        $client = User::factory()->create();
        Sanctum::actingAs($client);

        $this->postJson("/api/client/favorites/{$photographer->id}")->assertCreated();
        $this->postJson("/api/client/favorites/{$photographer->id}")->assertStatus(422);
    }

    public function test_cannot_favorite_a_client_account(): void
    {
        $otherClient = User::factory()->create();
        $client = User::factory()->create();
        Sanctum::actingAs($client);

        $this->postJson("/api/client/favorites/{$otherClient->id}")->assertStatus(422);
    }

    public function test_client_can_view_their_favorites(): void
    {
        $photographer = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($photographer)->approved()->create();
        $client = User::factory()->create();
        FavoritePhotographer::create(['client_id' => $client->id, 'photographer_id' => $photographer->id]);
        Sanctum::actingAs($client);

        $response = $this->getJson('/api/client/favorites');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.is_available'));
    }

    public function test_favorite_remains_after_photographer_becomes_unapproved(): void
    {
        $photographer = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($photographer)->pendingReview()->create();
        $client = User::factory()->create();
        FavoritePhotographer::create(['client_id' => $client->id, 'photographer_id' => $photographer->id]);
        Sanctum::actingAs($client);

        $response = $this->getJson('/api/client/favorites');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertFalse($response->json('data.0.is_available'));
    }

    public function test_client_can_remove_a_favorite(): void
    {
        $photographer = User::factory()->photographer()->create();
        $client = User::factory()->create();
        Sanctum::actingAs($client);
        $this->postJson("/api/client/favorites/{$photographer->id}")->assertCreated();

        $this->deleteJson("/api/client/favorites/{$photographer->id}")->assertOk();

        $this->assertDatabaseMissing('favorite_photographers', ['client_id' => $client->id, 'photographer_id' => $photographer->id]);
    }

    public function test_photographer_cannot_manage_favorites(): void
    {
        $target = User::factory()->photographer()->create();
        $photographer = User::factory()->photographer()->create();
        Sanctum::actingAs($photographer);

        $this->postJson("/api/client/favorites/{$target->id}")->assertStatus(403);
    }

    public function test_guest_cannot_access_favorites(): void
    {
        $this->getJson('/api/client/favorites')->assertStatus(401);
    }
}