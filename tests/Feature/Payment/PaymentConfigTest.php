<?php

namespace Tests\Feature\Payment;

use App\Models\PhotographerApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_eligible_photographer_can_create_payment_config(): void
    {
        Storage::fake('public');

        $photographer = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($photographer)->approved()->create();
        Sanctum::actingAs($photographer);

        $response = $this->postJson('/api/photographer/payment-config', [
            'gcash_account_name' => 'Juan Dela Cruz',
            'gcash_account_number' => '09171234567',
            'gcash_qr_code' => UploadedFile::fake()->image('qr.jpg'),
        ]);

        $response->assertOk()->assertJsonPath('data.gcash_account_name', 'Juan Dela Cruz');
        $this->assertDatabaseHas('photographer_payment_configs', [
            'user_id' => $photographer->id,
            'gcash_account_number' => '09171234567',
        ]);
    }

    public function test_config_requires_a_qr_code_on_first_creation(): void
    {
        Storage::fake('public');

        $photographer = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($photographer)->approved()->create();
        Sanctum::actingAs($photographer);

        $this->postJson('/api/photographer/payment-config', [
            'gcash_account_name' => 'Juan Dela Cruz',
            'gcash_account_number' => '09171234567',
        ])->assertStatus(422);
    }

    public function test_updating_account_details_does_not_require_re_uploading_qr(): void
    {
        Storage::fake('public');

        $photographer = User::factory()->photographer()->create();
        PhotographerApplication::factory()->for($photographer)->approved()->create();
        Sanctum::actingAs($photographer);

        $this->postJson('/api/photographer/payment-config', [
            'gcash_account_name' => 'Juan Dela Cruz',
            'gcash_account_number' => '09171234567',
            'gcash_qr_code' => UploadedFile::fake()->image('qr.jpg'),
        ])->assertOk();

        $this->postJson('/api/photographer/payment-config', [
            'gcash_account_name' => 'Juan D. Cruz',
            'gcash_account_number' => '09171234567',
        ])->assertOk()->assertJsonPath('data.gcash_account_name', 'Juan D. Cruz');
    }

    public function test_unapproved_photographer_cannot_create_payment_config(): void
    {
        Storage::fake('public');

        $photographer = User::factory()->photographer()->create();
        Sanctum::actingAs($photographer);

        $this->postJson('/api/photographer/payment-config', [
            'gcash_account_name' => 'Juan Dela Cruz',
            'gcash_account_number' => '09171234567',
            'gcash_qr_code' => UploadedFile::fake()->image('qr.jpg'),
        ])->assertStatus(422);
    }

    public function test_client_cannot_access_payment_config_endpoints(): void
    {
        $client = User::factory()->create();
        Sanctum::actingAs($client);

        $this->getJson('/api/photographer/payment-config')->assertStatus(403);
    }

    public function test_guest_cannot_access_payment_config_endpoints(): void
    {
        $this->getJson('/api/photographer/payment-config')->assertStatus(401);
    }
}