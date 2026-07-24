<?php

namespace App\Models;

use App\Enums\AddOnStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddOn extends Model
{
    use HasFactory;

    protected $table = 'add_ons';

    protected $fillable = ['user_id', 'name', 'description', 'price', 'status'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'status' => AddOnStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}