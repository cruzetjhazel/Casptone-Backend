<?php

namespace Tests\Feature\Admin;

use App\Models\PhotographerApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PhotographerApplicationReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_applications(): void
    {
        $admin = User::factory()->administrator()->create();
        $user = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($user)->pendingReview()->create();

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/photographer-applications')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_admin_can_view_a_single_application(): void
    {
        $admin = User::factory()->administrator()->create();
        $user = User::factory()->photographer()->create();
        $application = PhotographerApplication::factory()->for($user)->pendingReview()->create();

        Sanctum::actingAs($admin);

        $this->getJson("/api/admin/photographer-applications/{$application->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $application->id);
    }

    public function test_admin_can_approve_a_pending_application(): void
    {
        $admin = User::factory()->administrator()->create();
        $user = User::factory()->photographer()->create();
        $application = PhotographerApplication::factory()->for($user)->pendingReview()->create();

        Sanctum::actingAs($admin);

        $this->postJson("/api/admin/photographer-applications/{$application->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertTrue($user->refresh()->isApprovedPhotographer());
        $this->assertSame('active', $user->account_status->value);
    }

    public function test_admin_can_reject_a_pending_application_with_reason(): void
    {
        $admin = User::factory()->administrator()->create();
        $user = User::factory()->photographer()->create();
        $application = PhotographerApplication::factory()->for($user)->pendingReview()->create();

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/admin/photographer-applications/{$application->id}/reject", [
            'reason' => 'Documents unreadable.',
        ]);

        $response->assertOk()->assertJsonPath('data.status', 'rejected');
    }

    public function test_rejection_requires_a_reason(): void
    {
        $admin = User::factory()->administrator()->create();
        $user = User::factory()->photographer()->create();
        $application = PhotographerApplication::factory()->for($user)->pendingReview()->create();

        Sanctum::actingAs($admin);

        $this->postJson("/api/admin/photographer-applications/{$application->id}/reject", [])
            ->assertStatus(422);
    }

    public function test_admin_can_request_revision(): void
    {
        $admin = User::factory()->administrator()->create();
        $user = User::factory()->photographer()->create();
        $application = PhotographerApplication::factory()->for($user)->pendingReview()->create();

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/admin/photographer-applications/{$application->id}/request-revision", [
            'notes' => 'Please re-upload your ID.',
        ]);

        $response->assertOk()->assertJsonPath('data.status', 'revision_requested');
    }

    public function test_cannot_approve_a_draft_application(): void
    {
        $admin = User::factory()->administrator()->create();
        $user = User::factory()->photographer()->create();
        $application = PhotographerApplication::factory()->for($user)->create(); // draft

        Sanctum::actingAs($admin);

        $this->postJson("/api/admin/photographer-applications/{$application->id}/approve")
            ->assertStatus(422);
    }

    public function test_client_cannot_access_admin_review_endpoints(): void
    {
        $client = User::factory()->create();
        $user = User::factory()->photographer()->create();
        $application = PhotographerApplication::factory()->for($user)->pendingReview()->create();

        Sanctum::actingAs($client);

        $this->getJson('/api/admin/photographer-applications')->assertStatus(403);
        $this->postJson("/api/admin/photographer-applications/{$application->id}/approve")->assertStatus(403);
    }

    public function test_photographer_cannot_access_admin_review_endpoints(): void
    {
        $photographer = User::factory()->photographer()->create();
        $otherUser = User::factory()->photographer()->create();
        $application = PhotographerApplication::factory()->for($otherUser)->pendingReview()->create();

        Sanctum::actingAs($photographer);

        $this->getJson('/api/admin/photographer-applications')->assertStatus(403);
        $this->postJson("/api/admin/photographer-applications/{$application->id}/approve")->assertStatus(403);
    }

    public function test_photographer_cannot_view_another_photographers_application(): void
    {
        $viewer = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($viewer)->create();

        $owner = User::factory()->photographer()->create();
        $othersApplication = PhotographerApplication::factory()->for($owner)->pendingReview()->create();

        Sanctum::actingAs($viewer);

        // Self-service route always resolves the caller's own application (their draft),
        // never another photographer's — confirms ownership isolation.
        $response = $this->getJson('/api/photographer/application');
        $response->assertOk()->assertJsonPath('data.id', $viewer->photographerApplication->id);
        $response->assertJsonMissing(['data' => ['id' => $othersApplication->id]]);
    }

    public function test_account_status_and_application_status_are_independent(): void
    {
        $admin = User::factory()->administrator()->create();
        $user = User::factory()->photographer()->suspended()->create();
        $application = PhotographerApplication::factory()->for($user)->pendingReview()->create();

        Sanctum::actingAs($admin);

        $this->postJson("/api/admin/photographer-applications/{$application->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $user->refresh();
        $this->assertSame('approved', $user->photographerApplication->status->value);
        $this->assertSame('suspended', $user->account_status->value); // unaffected by approval
    }
}