<?php

namespace App\Models;

use App\Enums\PaymentPlan;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 'client_id', 'photographer_id',
        'type', 'method', 'plan', 'amount', 'reference_number', 'payment_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => PaymentType::class,
            'plan' => PaymentPlan::class,
            'amount' => 'decimal:2',
            'payment_date' => 'date:Y-m-d',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }
}