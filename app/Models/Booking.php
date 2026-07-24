<?php

namespace App\Models;

use App\Enums\BookingLocationType;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\CancellationDecision;
use App\Enums\PaymentPlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;
    protected $attributes = [
        'payment_status' => 'pending',
    ];

    protected $fillable = [
        'client_id', 'photographer_id', 'package_id',
        'is_custom_package', 'package_snapshot', 'custom_package_snapshot', 'add_ons_snapshot',
        'event_type', 'custom_event_type', 'event_date', 'start_time', 'end_time',
        'location_type', 'event_address', 'guest_count', 'special_requests',
        'subtotal', 'total_price', 'status', 'hold_expires_at',
        'rejection_reason', 'cancellation_reason', 'cancellation_requested_at',
        'cancellation_decision', 'cancellation_decided_at',
        'payment_plan', 'payment_status',
    ];

    protected function casts(): array
    {
        return [
            'is_custom_package' => 'boolean',
            'package_snapshot' => 'array',
            'custom_package_snapshot' => 'array',
            'add_ons_snapshot' => 'array',
            'event_date' => 'date:Y-m-d',
            'guest_count' => 'integer',
            'subtotal' => 'decimal:2',
            'total_price' => 'decimal:2',
            'status' => BookingStatus::class,
            'location_type' => BookingLocationType::class,
            'cancellation_decision' => CancellationDecision::class,
            'hold_expires_at' => 'datetime',
            'cancellation_requested_at' => 'datetime',
            'cancellation_decided_at' => 'datetime',
            'payment_plan' => PaymentPlan::class,
            'payment_status' => BookingPaymentStatus::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function photographer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'photographer_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isHoldExpired(): bool
    {
        return $this->status === BookingStatus::Pending
            && $this->hold_expires_at !== null
            && $this->hold_expires_at->isPast();
    }

    public function hasPendingCancellationRequest(): bool
    {
        return $this->cancellation_requested_at !== null && $this->cancellation_decision === null;
    }

    /**
     * The amount due online for a given payment plan (§8.2, §8.8).
     * Half Payment = 50% online + 50% remaining balance; Full Payment = 100% online.
     */
    public function onlineAmountDueFor(PaymentPlan $plan): float
    {
        return $plan === PaymentPlan::Full
            ? (float) $this->total_price
            : round((float) $this->total_price / 2, 2);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    /**
     * Total Booking Amount - Online Payment = Remaining Balance (§8.8).
     */
    public function remainingBalance(): float
    {
        return max(0.0, round((float) $this->total_price - $this->totalPaid(), 2));
    }

    /**
     * True once the required online payment for a Half-Payment booking has
     * been submitted (Confirmed + payment_plan=Half) but the onsite
     * remaining balance hasn't been recorded yet (§8.9).
     */
    public function isEligibleForOnsitePayment(): bool
    {
        return $this->status === BookingStatus::Confirmed
            && $this->payment_plan === PaymentPlan::Half
            && $this->payment_status !== BookingPaymentStatus::FullyPaid;
    }
}