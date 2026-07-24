<?php

namespace Database\Factories;

use App\Enums\BookingLocationType;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $package = Package::factory()->published();

        return [
            'client_id' => User::factory(),
            'photographer_id' => User::factory()->photographer(),
            'package_id' => $package,
            'is_custom_package' => false,
            'package_snapshot' => ['name' => 'Basic Wedding Package', 'price' => 10000],
            'add_ons_snapshot' => [],
            'event_type' => 'wedding',
            'event_date' => now()->addDays(10)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '11:30',
            'location_type' => BookingLocationType::Studio,
            'subtotal' => 10000,
            'total_price' => 10000,
            'status' => BookingStatus::Pending,
            'hold_expires_at' => now()->addHours(24),
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn () => ['status' => BookingStatus::Accepted, 'hold_expires_at' => null]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => BookingStatus::Rejected,
            'rejection_reason' => 'Not available for this date.',
            'hold_expires_at' => null,
        ]);
    }
}