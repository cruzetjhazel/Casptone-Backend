<?php

namespace App\Models;

use App\Enums\PortfolioImageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotographerPortfolioImage extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'path', 'status'];

    protected function casts(): array
    {
        return [
            'status' => PortfolioImageStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}