<?php

namespace App\Models;

use App\Enums\AddOnStatus;
use App\Enums\CustomPackageComponentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomPackageComponent extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'type', 'label', 'price_addition', 'status'];

    protected function casts(): array
    {
        return [
            'type' => CustomPackageComponentType::class,
            'price_addition' => 'decimal:2',
            'status' => AddOnStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}