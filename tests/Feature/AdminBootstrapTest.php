<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminBootstrapTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_create_command_creates_an_administrator(): void
    {
        $this->artisan('admin:create', ['--name' => 'Root Admin', '--email' => 'root@example.com'])
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'root@example.com',
            'account_type' => 'administrator',
            'account_status' => 'active',
        ]);
    }

    public function test_admin_create_rejects_a_duplicate_email(): void
    {
        User::factory()->administrator()->create(['email' => 'root@example.com']);

        $this->artisan('admin:create', ['--name' => 'Root Admin', '--email' => 'root@example.com'])
            ->assertExitCode(1);
    }

    public function test_generated_password_is_never_stored_in_plaintext(): void
    {
        Artisan::call('admin:create', ['--name' => 'Root Admin', '--email' => 'root2@example.com']);
        $output = Artisan::output();

        $admin = User::where('email', 'root2@example.com')->first();

        $this->assertStringContainsString('Password:', $output);
        $this->assertNotEquals($output, $admin->password);
    }
}