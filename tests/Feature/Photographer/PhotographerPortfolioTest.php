<?php

namespace Tests\Feature\Photographer;

use App\Enums\PortfolioImageStatus;
use App\Models\PhotographerPortfolioImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhotographerPortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_photographer_can_upload_portfolio_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->photographer()->create();
        Sanctum::actingAs($user);

        $response = $this->post('/api/photographer/portfolio', [
            'image' => UploadedFile::fake()->image('shot.jpg'),
        ]);

        $response->assertCreated()->assertJsonPath('success', true);
        $this->assertDatabaseHas('photographer_portfolio_images', ['user_id' => $user->id, 'status' => 'active']);
    }

    public function test_upload_validation_rejects_non_image_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->photographer()->create();
        Sanctum::actingAs($user);

        $response = $this->post('/api/photographer/portfolio', [
            'image' => UploadedFile::fake()->create('document.pdf', 100),
        ]);

        $response->assertStatus(422);
    }

    public function test_photographer_can_view_own_portfolio(): void
    {
        $user = User::factory()->photographer()->create();
        PhotographerPortfolioImage::factory()->for($user)->count(3)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/photographer/portfolio');

        $response->assertOk();
        $this->assertCount(3, $response->json('data'));
    }

    public function test_photographer_cannot_manage_another_photographers_portfolio(): void
    {
        $owner = User::factory()->photographer()->create();
        $image = PhotographerPortfolioImage::factory()->for($owner)->create();

        $other = User::factory()->photographer()->create();
        Sanctum::actingAs($other);

        $this->postJson("/api/photographer/portfolio/{$image->id}/archive")->assertStatus(403);
        $this->deleteJson("/api/photographer/portfolio/{$image->id}")->assertStatus(403);
    }

    public function test_photographer_can_archive_and_restore_an_image(): void
    {
        $user = User::factory()->photographer()->create();
        PhotographerPortfolioImage::factory()->for($user)->count(7)->create();
        $image = $user->portfolioImages()->first();

        Sanctum::actingAs($user);

        $this->postJson("/api/photographer/portfolio/{$image->id}/archive")
            ->assertOk()
            ->assertJsonPath('data.status', 'archived');

        $this->postJson("/api/photographer/portfolio/{$image->id}/restore")
            ->assertOk()
            ->assertJsonPath('data.status', 'active');
    }

    public function test_cannot_archive_below_the_minimum_of_six_active_images(): void
    {
        $user = User::factory()->photographer()->create();
        PhotographerPortfolioImage::factory()->for($user)->count(6)->create();
        $image = $user->portfolioImages()->first();

        Sanctum::actingAs($user);

        $this->postJson("/api/photographer/portfolio/{$image->id}/archive")->assertStatus(422);
    }

    public function test_can_archive_freely_above_the_minimum(): void
    {
        $user = User::factory()->photographer()->create();
        PhotographerPortfolioImage::factory()->for($user)->count(7)->create();
        $image = $user->portfolioImages()->first();

        Sanctum::actingAs($user);

        $this->postJson("/api/photographer/portfolio/{$image->id}/archive")->assertOk();
    }

    public function test_cannot_exceed_the_maximum_of_twelve_active_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->photographer()->create();
        PhotographerPortfolioImage::factory()->for($user)->count(12)->create();
        Sanctum::actingAs($user);

        $response = $this->post('/api/photographer/portfolio', [
            'image' => UploadedFile::fake()->image('overflow.jpg'),
        ]);

        $response->assertStatus(422);
    }

    public function test_restoring_cannot_exceed_the_maximum_either(): void
    {
        $user = User::factory()->photographer()->create();
        PhotographerPortfolioImage::factory()->for($user)->count(12)->create();
        $archived = PhotographerPortfolioImage::factory()->for($user)->archived()->create();

        Sanctum::actingAs($user);

        $this->postJson("/api/photographer/portfolio/{$archived->id}/restore")->assertStatus(422);
    }

    public function test_can_permanently_delete_an_archived_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->photographer()->create();
        $image = PhotographerPortfolioImage::factory()->for($user)->archived()->create();

        Sanctum::actingAs($user);

        $this->deleteJson("/api/photographer/portfolio/{$image->id}")->assertOk();
        $this->assertDatabaseMissing('photographer_portfolio_images', ['id' => $image->id]);
    }

    public function test_cannot_permanently_delete_an_active_image(): void
    {
        $user = User::factory()->photographer()->create();
        $image = PhotographerPortfolioImage::factory()->for($user)->create(); // active

        Sanctum::actingAs($user);

        $this->deleteJson("/api/photographer/portfolio/{$image->id}")->assertStatus(422);
        $this->assertDatabaseHas('photographer_portfolio_images', ['id' => $image->id]);
    }

    public function test_administrator_can_archive_for_moderation(): void
    {
        $admin = User::factory()->administrator()->create();
        $user = User::factory()->photographer()->create();
        PhotographerPortfolioImage::factory()->for($user)->count(7)->create();
        $image = $user->portfolioImages()->first();

        Sanctum::actingAs($admin);

        $this->postJson("/api/admin/photographer-portfolio-images/{$image->id}/archive")
            ->assertOk()
            ->assertJsonPath('data.status', 'archived');
    }

    public function test_client_cannot_access_portfolio_endpoints(): void
    {
        $client = User::factory()->create();
        Sanctum::actingAs($client);

        $this->getJson('/api/photographer/portfolio')->assertStatus(403);
    }
}