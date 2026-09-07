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

    protected $fillable = ['user_id', 'type', 'tier_name', 'label', 'price_addition', 'duration_minutes', 'status'];

    protected function casts(): array
    {
        return [
            'type' => CustomPackageComponentType::class,
            'price_addition' => 'decimal:2',
            // Null on every component except the ones a photographer sets up
            // to represent a selectable coverage duration (see the
            // 2026_09_07_000000_add_duration_to_custom_package_tables
            // migration and CreateBookingAction::resolveCustomPackage()).
            'duration_minutes' => 'integer',
            'status' => AddOnStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}