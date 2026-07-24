<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_be_created_with_required_fields(): void
    {
        $user = User::create([
            'name' => 'Jane Dela Cruz',
            'email' => 'jane@example.com',
            'password' => 'Secret123!',
            'account_type' => AccountType::Client,
            'account_status' => AccountStatus::Active,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'account_type' => 'client',
            'account_status' => 'active',
        ]);
        $this->assertTrue(Hash::check('Secret123!', $user->password));
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'dupe@example.com']);

        $this->expectException(QueryException::class);

        User::create([
            'name' => 'Dup',
            'email' => 'dupe@example.com',
            'password' => 'Secret123!',
            'account_type' => AccountType::Client,
            'account_status' => AccountStatus::Active,
        ]);
    }

    public function test_database_rejects_an_invalid_account_type(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('CHECK constraint only enforced on MySQL.');
        }

        $this->expectException(QueryException::class);

        DB::table('users')->insert([
            'name' => 'Bad Actor',
            'email' => 'bad@example.com',
            'password' => bcrypt('x'),
            'account_type' => 'hacker',
            'account_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_database_rejects_an_invalid_account_status(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->markTestSkipped('CHECK constraint only enforced on MySQL.');
        }

        $this->expectException(QueryException::class);

        DB::table('users')->insert([
            'name' => 'Bad Status',
            'email' => 'badstatus@example.com',
            'password' => bcrypt('x'),
            'account_type' => 'client',
            'account_status' => 'on_vacation',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}