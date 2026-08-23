<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileView extends Model
{
    // Rows are dedupe records, not timestamps-tracked entities — no updated_at.
    public $timestamps = false;

    protected $fillable = [
        'photographer_id', 'viewer_hash', 'viewed_on',
    ];

    protected function casts(): array
    {
        return [
            'viewed_on' => 'date:Y-m-d',
        ];
    }

    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }
}