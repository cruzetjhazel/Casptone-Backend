<?php

namespace App\Actions\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateAdministratorAction
{
    public function execute(string $name, string $email, string $password): User
    {
        return DB::transaction(function () use ($name, $email, $password) {
            return User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password, // hashed automatically via the model cast
                'account_type' => AccountType::Administrator,
                'account_status' => AccountStatus::Active,
                'email_verified_at' => now(),
            ]);
        });
    }
}