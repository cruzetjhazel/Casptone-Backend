<?php

namespace App\Models;

use App\Enums\PaymentType;
use App\Enums\PhotographerPaymentReferenceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotographerPaymentReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'photographer_id', 'reference_number', 'amount_received',
        'payment_date', 'payment_type', 'status',
    ];

    protected function casts(): array
    {
        return [
            'amount_received' => 'decimal:2',
            'payment_date' => 'date:Y-m-d',
            'payment_type' => PaymentType::class,
            'status' => PhotographerPaymentReferenceStatus::class,
        ];
    }

    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }
}