<?php

namespace App\Console\Commands;

use App\Actions\Auth\CreateAdministratorAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreateAdministratorCommand extends Command
{
    protected $signature = 'admin:create
                            {--name= : Full name of the administrator}
                            {--email= : Email address of the administrator}
                            {--force : Skip the production confirmation prompt}';

    protected $description = 'Securely bootstrap an Administrator account. Generates a random password — never hardcode one.';

    public function handle(CreateAdministratorAction $action): int
    {
        if (App::environment('production') && ! $this->option('force')) {
            if (! $this->confirm('You are running this in PRODUCTION. Create an administrator account?')) {
                $this->warn('Aborted.');
                return self::FAILURE;
            }
        }

        $name = $this->option('name') ?: $this->ask('Administrator name');
        $email = $this->option('email') ?: $this->ask('Administrator email');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $password = Str::password(20);

        $admin = $action->execute($name, $email, $password);

        $this->newLine();
        $this->info('Administrator account created successfully.');
        $this->line('Email:    '.$admin->email);
        $this->line('Password: '.$password);
        $this->warn('Copy this password now. It is not stored anywhere and will not be shown again.');

        return self::SUCCESS;
    }
}