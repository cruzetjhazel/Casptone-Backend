<?php

namespace Tests\Feature\Photographer;

use App\Models\BlockedDate;
use App\Models\PhotographerApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BlockedDateTest extends TestCase
{
    use RefreshDatabase;

    protected function approvedPhotographer(): User
    {
        $user = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($user)->approved()->create();

        return $user;
    }

    public function test_eligible_photographer_can_create_a_full_day_block(): void
    {
        $user = $this->approvedPhotographer();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/photographer/blocked-dates', [
            'date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertCreated()->assertJsonPath('data.full_day', true);
    }

    public function test_photographer_can_create_a_partial_block(): void
    {
        $user = $this->approvedPhotographer();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/photographer/blocked-dates', [
            'date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '12:00',
            'end_time' => '13:00',
            'reason' => 'Lunch break',
        ]);

        $response->assertCreated()->assertJsonPath('data.full_day', false);
    }

    public function test_client_cannot_create_blocked_dates(): void
    {
        $client = User::factory()->create();
        Sanctum::actingAs($client);

        $this->postJson('/api/photographer/blocked-dates', ['date' => now()->addDay()->format('Y-m-d')])
            ->assertStatus(403);
    }

    public function test_start_time_without_end_time_is_rejected(): void
    {
        $user = $this->approvedPhotographer();
        Sanctum::actingAs($user);

        $this->postJson('/api/photographer/blocked-dates', [
            'date' => now()->addDay()->format('Y-m-d'),
            'start_time' => '12:00',
        ])->assertStatus(422);
    }

    public function test_full_day_block_conflicts_with_any_other_block_that_day(): void
    {
        $user = $this->approvedPhotographer();
        BlockedDate::factory()->for($user)->create(['date' => now()->addDays(5)->format('Y-m-d')]);
        Sanctum::actingAs($user);

        $this->postJson('/api/photographer/blocked-dates', [
            'date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
        ])->assertStatus(422);
    }

    public function test_overlapping_partial_blocks_are_rejected(): void
    {
        $user = $this->approvedPhotographer();
        BlockedDate::factory()->for($user)->partial()->create(['date' => now()->addDays(5)->format('Y-m-d')]);
        Sanctum::actingAs($user);

        $this->postJson('/api/photographer/blocked-dates', [
            'date' => now()->addDays(5)->format('Y-m-d'),
            'start_time' => '12:30',
            'end_time' => '13:30',
        ])->assertStatus(422);
    }

    public function test_photographer_cannot_manage_another_photographers_blocked_date(): void
    {
        $owner = $this->approvedPhotographer();
        $block = BlockedDate::factory()->for($owner)->create();

        $other = $this->approvedPhotographer();
        Sanctum::actingAs($other);

        $this->deleteJson("/api/photographer/blocked-dates/{$block->id}")->assertStatus(403);
    }

    public function test_photographer_can_delete_a_blocked_date(): void
    {
        $user = $this->approvedPhotographer();
        $block = BlockedDate::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->deleteJson("/api/photographer/blocked-dates/{$block->id}")->assertOk();
        $this->assertDatabaseMissing('blocked_dates', ['id' => $block->id]);
    }
}