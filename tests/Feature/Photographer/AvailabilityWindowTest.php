<?php

namespace Tests\Feature\Photographer;

use App\Models\AvailabilityWindow;
use App\Models\PhotographerApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AvailabilityWindowTest extends TestCase
{
    use RefreshDatabase;

    protected function approvedPhotographer(): User
    {
        $user = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($user)->approved()->create();

        return $user;
    }

    protected function validPayload(): array
    {
        return [
            'date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ];
    }

    public function test_guest_cannot_access_availability_endpoints(): void
    {
        $this->getJson('/api/photographer/availability-windows')->assertStatus(401);
    }

    public function test_eligible_photographer_can_create_availability_window(): void
    {
        $user = $this->approvedPhotographer();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/photographer/availability-windows', $this->validPayload());

        $response->assertCreated();
        $this->assertDatabaseHas('availability_windows', ['user_id' => $user->id]);
    }

    public function test_unapproved_photographer_cannot_create_availability_window(): void
    {
        $user = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($user)->pendingReview()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/photographer/availability-windows', $this->validPayload())->assertStatus(403);
    }

    public function test_client_cannot_manage_availability(): void
    {
        $client = User::factory()->create();
        Sanctum::actingAs($client);

        $this->postJson('/api/photographer/availability-windows', $this->validPayload())->assertStatus(403);
    }

    public function test_past_date_is_rejected(): void
    {
        $user = $this->approvedPhotographer();
        Sanctum::actingAs($user);

        $payload = $this->validPayload();
        $payload['date'] = now()->subDay()->format('Y-m-d');

        $this->postJson('/api/photographer/availability-windows', $payload)->assertStatus(422);
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $user = $this->approvedPhotographer();
        Sanctum::actingAs($user);

        $payload = $this->validPayload();
        $payload['start_time'] = '17:00';
        $payload['end_time'] = '09:00';

        $this->postJson('/api/photographer/availability-windows', $payload)->assertStatus(422);
    }

    public function test_overlapping_window_is_rejected(): void
    {
        $user = $this->approvedPhotographer();
        AvailabilityWindow::factory()->for($user)->create($this->validPayload());
        Sanctum::actingAs($user);

        $overlapping = $this->validPayload();
        $overlapping['start_time'] = '10:00';
        $overlapping['end_time'] = '11:00';

        $this->postJson('/api/photographer/availability-windows', $overlapping)->assertStatus(422);
    }

    public function test_non_overlapping_window_same_day_is_allowed(): void
    {
        $user = $this->approvedPhotographer();
        AvailabilityWindow::factory()->for($user)->create([
            'date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '12:00',
        ]);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/photographer/availability-windows', [
            'date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '13:00',
            'end_time' => '17:00',
        ]);

        $response->assertCreated();
    }

    public function test_photographer_can_view_their_own_windows(): void
    {
        $user = $this->approvedPhotographer();
        AvailabilityWindow::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/photographer/availability-windows');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    public function test_photographer_cannot_manage_another_photographers_window(): void
    {
        $owner = $this->approvedPhotographer();
        $window = AvailabilityWindow::factory()->for($owner)->create();

        $other = $this->approvedPhotographer();
        Sanctum::actingAs($other);

        $this->patchJson("/api/photographer/availability-windows/{$window->id}", $this->validPayload())->assertStatus(403);
        $this->deleteJson("/api/photographer/availability-windows/{$window->id}")->assertStatus(403);
    }

    public function test_photographer_can_update_their_own_window(): void
    {
        $user = $this->approvedPhotographer();
        $window = AvailabilityWindow::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $response = $this->patchJson("/api/photographer/availability-windows/{$window->id}", [
            'date' => $window->date->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '18:00',
        ]);

        $response->assertOk()->assertJsonPath('data.start_time', '10:00:00');
    }

    public function test_photographer_can_delete_their_own_window(): void
    {
        $user = $this->approvedPhotographer();
        $window = AvailabilityWindow::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->deleteJson("/api/photographer/availability-windows/{$window->id}")->assertOk();
        $this->assertDatabaseMissing('availability_windows', ['id' => $window->id]);
    }
}