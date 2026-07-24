<?php

namespace Tests\Feature;

use App\Models\AvailabilityWindow;
use App\Models\BlockedDate;
use App\Models\Package;
use App\Models\PhotographerApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function approvedPhotographer(): User
    {
        $user = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($user)->approved()->create();

        return $user;
    }

    public function test_public_slot_generation_respects_duration_and_buffer(): void
    {
        $user = $this->approvedPhotographer();
        $package = Package::factory()->for($user)->published()->create([
            'duration_minutes' => 120,
            'buffer_minutes' => 30,
        ]);
        $date = now()->addDays(7)->format('Y-m-d');
        AvailabilityWindow::factory()->for($user)->create([
            'date' => $date, 'start_time' => '09:00', 'end_time' => '13:00',
        ]);

        $response = $this->getJson("/api/photographers/{$user->id}/availability/slots?date={$date}&package_id={$package->id}");

        $response->assertOk();
        // 09:00-13:00 window, 150 min needed (120+30) -> last valid start is 10:30 (10:30+150=13:00)
        $this->assertContains('09:00', $response->json('data.start_times'));
        $this->assertContains('10:30', $response->json('data.start_times'));
        $this->assertNotContains('11:00', $response->json('data.start_times'));
    }

    public function test_blocked_period_removes_overlapping_slots(): void
    {
        $user = $this->approvedPhotographer();
        $package = Package::factory()->for($user)->published()->create([
            'duration_minutes' => 60, 'buffer_minutes' => 0,
        ]);
        $date = now()->addDays(7)->format('Y-m-d');
        AvailabilityWindow::factory()->for($user)->create(['date' => $date, 'start_time' => '09:00', 'end_time' => '12:00']);
        BlockedDate::factory()->for($user)->create(['date' => $date, 'start_time' => '10:00', 'end_time' => '11:00']);

        $response = $this->getJson("/api/photographers/{$user->id}/availability/slots?date={$date}&package_id={$package->id}");

        $response->assertOk();
        $this->assertNotContains('10:00', $response->json('data.start_times'));
        $this->assertContains('09:00', $response->json('data.start_times'));
        $this->assertContains('11:00', $response->json('data.start_times'));
    }

    public function test_month_calendar_distinguishes_available_partial_and_unavailable(): void
    {
        $user = $this->approvedPhotographer();
        $package = Package::factory()->for($user)->published()->create(['duration_minutes' => 60, 'buffer_minutes' => 0]);

        $availableDate = now()->addDays(3);
        $partialDate = now()->addDays(4);
        $unavailableDate = now()->addDays(5);

        AvailabilityWindow::factory()->for($user)->create(['date' => $availableDate->format('Y-m-d'), 'start_time' => '09:00', 'end_time' => '17:00']);
        AvailabilityWindow::factory()->for($user)->create(['date' => $partialDate->format('Y-m-d'), 'start_time' => '09:00', 'end_time' => '17:00']);
        BlockedDate::factory()->for($user)->create(['date' => $partialDate->format('Y-m-d'), 'start_time' => '09:00', 'end_time' => '10:00']);
        // unavailableDate has no window at all

        $response = $this->getJson("/api/photographers/{$user->id}/availability/calendar?month={$availableDate->format('Y-m')}&package_id={$package->id}");

        $response->assertOk();
        $data = $response->json('data');
        $this->assertSame('available', $data[$availableDate->format('Y-m-d')]);
        $this->assertSame('partial', $data[$partialDate->format('Y-m-d')]);
        $this->assertSame('unavailable', $data[$unavailableDate->format('Y-m-d')]);
    }

    public function test_public_availability_endpoints_404_for_unapproved_photographer(): void
    {
        $user = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($user)->pendingReview()->create();
        $package = Package::factory()->for($user)->published()->create();

        $this->getJson("/api/photographers/{$user->id}/availability/slots?date=".now()->addDay()->format('Y-m-d')."&package_id={$package->id}")
            ->assertStatus(404);
    }

    public function test_package_from_another_photographer_is_rejected(): void
    {
        $user = $this->approvedPhotographer();
        $otherPhotographer = $this->approvedPhotographer();
        $foreignPackage = Package::factory()->for($otherPhotographer)->published()->create();

        $this->getJson("/api/photographers/{$user->id}/availability/slots?date=".now()->addDay()->format('Y-m-d')."&package_id={$foreignPackage->id}")
            ->assertStatus(404);
    }
}