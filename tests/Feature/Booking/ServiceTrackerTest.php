<?php

namespace Tests\Feature\Booking;

use App\Models\Booking;
use App\Models\PhotographerApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServiceTrackerTest extends TestCase
{
    use RefreshDatabase;

    protected function approvedPhotographer(): User
    {
        $user = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($user)->approved()->create();

        return $user;
    }

    public function test_guest_cannot_update_service_tracker(): void
    {
        $photographer = User::factory()->photographer()->create();
        $booking = Booking::factory()->confirmed()->create(['photographer_id' => $photographer->id]);

        $this->patchJson("/api/photographer/bookings/{$booking->id}/service-tracker", [
            'service_status' => 'event_day',
        ])->assertStatus(401);
    }

    public function test_photographer_can_advance_the_service_tracker(): void
    {
        $photographer = $this->approvedPhotographer();
        $booking = Booking::factory()->confirmed()->create(['photographer_id' => $photographer->id]);
        Sanctum::actingAs($photographer);

        $response = $this->patchJson("/api/photographer/bookings/{$booking->id}/service-tracker", [
            'service_status' => 'event_day',
        ]);

        $response->assertOk()->assertJsonPath('data.service_status', 'event_day');
        $this->assertNotNull($booking->fresh()->service_status_updated_at);
    }

    public function test_service_tracker_walks_through_every_stage_in_order(): void
    {
        $photographer = $this->approvedPhotographer();
        $booking = Booking::factory()->confirmed()->create(['photographer_id' => $photographer->id]);
        Sanctum::actingAs($photographer);

        $stages = ['upcoming', 'event_day', 'in_progress', 'photo_editing', 'ready_for_release', 'completed'];

        foreach ($stages as $stage) {
            $this->patchJson("/api/photographer/bookings/{$booking->id}/service-tracker", [
                'service_status' => $stage,
            ])->assertOk()->assertJsonPath('data.service_status', $stage);
        }

        $this->assertSame('completed', $booking->fresh()->status->value);
    }

    public function test_reaching_completed_stage_completes_the_booking(): void
    {
        $photographer = $this->approvedPhotographer();
        $booking = Booking::factory()->confirmed()->create(['photographer_id' => $photographer->id]);
        Sanctum::actingAs($photographer);

        $this->patchJson("/api/photographer/bookings/{$booking->id}/service-tracker", [
            'service_status' => 'completed',
        ])->assertOk()->assertJsonPath('data.status', 'completed');

        $this->assertSame('completed', $booking->fresh()->status->value);
    }

    public function test_moving_tracker_back_off_completed_uncompletes_the_booking(): void
    {
        $photographer = $this->approvedPhotographer();
        $booking = Booking::factory()->completed()->create([
            'photographer_id' => $photographer->id,
            'service_status' => 'completed',
        ]);
        Sanctum::actingAs($photographer);

        $response = $this->patchJson("/api/photographer/bookings/{$booking->id}/service-tracker", [
            'service_status' => 'ready_for_release',
        ]);

        $response->assertOk()->assertJsonPath('data.status', 'confirmed');
        $this->assertSame('confirmed', $booking->fresh()->status->value);
    }

    public function test_invalid_service_status_is_rejected(): void
    {
        $photographer = $this->approvedPhotographer();
        $booking = Booking::factory()->confirmed()->create(['photographer_id' => $photographer->id]);
        Sanctum::actingAs($photographer);

        $this->patchJson("/api/photographer/bookings/{$booking->id}/service-tracker", [
            'service_status' => 'not_a_real_stage',
        ])->assertStatus(422);
    }

    public function test_service_status_is_required(): void
    {
        $photographer = $this->approvedPhotographer();
        $booking = Booking::factory()->confirmed()->create(['photographer_id' => $photographer->id]);
        Sanctum::actingAs($photographer);

        $this->patchJson("/api/photographer/bookings/{$booking->id}/service-tracker", [])
            ->assertStatus(422);
    }

    public function test_cannot_manage_tracker_on_a_pending_booking(): void
    {
        $photographer = $this->approvedPhotographer();
        $booking = Booking::factory()->create(['photographer_id' => $photographer->id]); // default status: pending
        Sanctum::actingAs($photographer);

        $this->patchJson("/api/photographer/bookings/{$booking->id}/service-tracker", [
            'service_status' => 'event_day',
        ])->assertStatus(422);
    }

    public function test_cannot_manage_tracker_on_an_accepted_but_unconfirmed_booking(): void
    {
        $photographer = $this->approvedPhotographer();
        $booking = Booking::factory()->accepted()->create(['photographer_id' => $photographer->id]);
        Sanctum::actingAs($photographer);

        $this->patchJson("/api/photographer/bookings/{$booking->id}/service-tracker", [
            'service_status' => 'event_day',
        ])->assertStatus(422);
    }

    public function test_other_photographer_cannot_manage_the_tracker(): void
    {
        $owner = $this->approvedPhotographer();
        $booking = Booking::factory()->confirmed()->create(['photographer_id' => $owner->id]);

        $other = $this->approvedPhotographer();
        Sanctum::actingAs($other);

        $this->patchJson("/api/photographer/bookings/{$booking->id}/service-tracker", [
            'service_status' => 'event_day',
        ])->assertStatus(403);
    }

    public function test_unapproved_photographer_cannot_manage_the_tracker(): void
    {
        $photographer = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($photographer)->pendingReview()->create();
        $booking = Booking::factory()->confirmed()->create(['photographer_id' => $photographer->id]);
        Sanctum::actingAs($photographer);

        $this->patchJson("/api/photographer/bookings/{$booking->id}/service-tracker", [
            'service_status' => 'event_day',
        ])->assertStatus(403);
    }

    public function test_client_cannot_manage_the_service_tracker(): void
    {
        $client = User::factory()->create();
        $booking = Booking::factory()->create(['client_id' => $client->id]);
        Sanctum::actingAs($client);

        $this->patchJson("/api/photographer/bookings/{$booking->id}/service-tracker", [
            'service_status' => 'event_day',
        ])->assertStatus(403);
    }
}