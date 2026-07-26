<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotographerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'bio', 'style',
        'profile_photo_path', 'cover_photo_path',
        'facebook', 'instagram', 'website',
    ];

    protected function casts(): array
    {
        return [
            'style' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasRequiredSocialLink(): bool
    {
        return filled($this->facebook) || filled($this->instagram) || filled($this->website);
    }

    public function isComplete(): bool
    {
        return filled($this->bio)
            && filled($this->style)
            && filled($this->profile_photo_path)
            && filled($this->cover_photo_path)
            && $this->hasRequiredSocialLink();
    }
}