<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'account_type',
        'account_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'account_type' => AccountType::class,
            'account_status' => AccountStatus::class,
        ];
    }

    public function isAdministrator(): bool
    {
        return $this->account_type === AccountType::Administrator;
    }

    public function isPhotographer(): bool
    {
        return $this->account_type === AccountType::Photographer;
    }

    public function isClient(): bool
    {
        return $this->account_type === AccountType::Client;
    }

    public function isActive(): bool
    {
        return $this->account_status === AccountStatus::Active;
    }
    public function photographerApplication(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\PhotographerApplication::class);
    }

    public function isApprovedPhotographer(): bool
    {
        return $this->photographerApplication?->status === \App\Enums\PhotographerApplicationStatus::Approved;
    }
    public function photographerProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\PhotographerProfile::class);
    }

    public function portfolioImages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PhotographerPortfolioImage::class);
    }

    public function activePortfolioImageCount(): int
    {
        return $this->portfolioImages()
            ->where('status', \App\Enums\PortfolioImageStatus::Active)
            ->count();
    }
}