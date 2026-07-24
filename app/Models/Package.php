<?php

namespace App\Models;

use App\Enums\PackageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'description', 'included_items',
        'price', 'duration_minutes', 'buffer_minutes', 'status',
    ];

    protected function casts(): array
    {
        return [
            'included_items' => 'array',
            'price' => 'decimal:2',
            'duration_minutes' => 'integer',
            'buffer_minutes' => 'integer',
            'status' => PackageStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isEditable(): bool
    {
        return $this->status !== PackageStatus::Archived;
    }
}