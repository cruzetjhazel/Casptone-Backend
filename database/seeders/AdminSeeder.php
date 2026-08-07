<?php

namespace Database\Seeders;

use App\Actions\Auth\CreateAdministratorAction;
use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(CreateAdministratorAction $action): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command->warn('AdminSeeder only runs in local/testing. Use `php artisan admin:create` elsewhere.');
            return;
        }

        $name = 'admin';
        $email = env('ADMIN_EMAIL', 'admin@example.test');
        $password = env('ADMIN_PASSWORD') ?: Str::password(20);

        $existing = User::where('account_type', AccountType::Administrator)->first();

        if ($existing) {
            $existing->forceFill([
                'name' => $name,
                'email' => $email,
            ])->save();

            $this->command->info("Normalized administrator: {$email}");
            return;
        }

        $action->execute($name, $email, $password);

        $this->command->info("Seeded administrator: {$email}");
    }
}