<?php

namespace App\Actions\Booking;

use App\Enums\AddOnStatus;
use App\Enums\BookingLocationType;
use App\Enums\BookingStatus;
use App\Enums\PackageStatus;
use App\Models\Booking;
use App\Models\User;
use App\Services\Photographer\AvailabilityService;
use App\Services\Photographer\BookabilityService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class CreateBookingAction
{
    public function __construct(
        protected BookabilityService $bookabilityService,
        protected AvailabilityService $availabilityService,
    ) {
    }

    public function execute(User $client, array $data): Booking
    {
        $photographer = User::findOrFail($data['photographer_id']);

        if (! $this->bookabilityService->isBookable($photographer)) {
            throw ValidationException::withMessages([
                'photographer_id' => ['This photographer is not currently bookable.'],
            ]);
        }

        $isCustom = (bool) ($data['is_custom_package'] ?? false);

        if ($isCustom) {
            [$neededMinutes, $subtotal, $packageId, $packageSnapshot, $customSnapshot] = $this->resolveCustomPackage($photographer, $data);
        } else {
            [$neededMinutes, $subtotal, $packageId, $packageSnapshot, $customSnapshot] = $this->resolveFixedPackage($photographer, $data);
        }

        [$addOnsSnapshot, $addOnsTotal] = $this->resolveAddOns($photographer, $data['add_on_ids'] ?? []);
        $subtotal += $addOnsTotal;

        $this->assertSlotIsAvailable($photographer, $data['event_date'], $data['start_time'], $neededMinutes);

        $endTime = Carbon::parse($data['start_time'])->addMinutes($neededMinutes)->format('H:i');

        $this->assertNoConflict($photographer, $data['event_date'], $data['start_time'], $endTime);

        return Booking::create([
            'client_id' => $client->id,
            'photographer_id' => $photographer->id,
            'package_id' => $packageId,
            'is_custom_package' => $isCustom,
            'package_snapshot' => $packageSnapshot,
            'custom_package_snapshot' => $customSnapshot,
            'add_ons_snapshot' => $addOnsSnapshot,
            'event_type' => $data['event_type'],
            'custom_event_type' => $data['custom_event_type'] ?? null,
            'event_date' => $data['event_date'],
            'start_time' => $data['start_time'],
            'end_time' => $endTime,
            'location_type' => $data['location_type'],
            'event_address' => $data['event_address'] ?? null,
            'guest_count' => $data['guest_count'] ?? null,
            'special_requests' => $data['special_requests'] ?? null,
            'subtotal' => $subtotal,
            'total_price' => $subtotal,
            'status' => BookingStatus::Pending,
            'hold_expires_at' => now()->addHours(24),
        ]);
    }

    protected function resolveFixedPackage(User $photographer, array $data): array
    {
        $package = $photographer->packages()->where('status', PackageStatus::Published)->find($data['package_id'] ?? null);

        if (! $package) {
            throw ValidationException::withMessages([
                'package_id' => ['This package is not available for this photographer.'],
            ]);
        }

        $neededMinutes = $package->duration_minutes + $package->buffer_minutes;

        $snapshot = [
            'name' => $package->name,
            'description' => $package->description,
            'price' => (string) $package->price,
            'duration_minutes' => $package->duration_minutes,
            'buffer_minutes' => $package->buffer_minutes,
        ];

        return [$neededMinutes, (float) $package->price, $package->id, $snapshot, null];
    }

    protected function resolveCustomPackage(User $photographer, array $data): array
    {
        $config = $photographer->customPackageConfig;

        if (! $config || ! $config->enabled) {
            throw ValidationException::withMessages([
                'is_custom_package' => ['Custom packages are not enabled for this photographer.'],
            ]);
        }

        $componentIds = $data['custom_component_ids'] ?? [];
        $components = $photographer->customPackageComponents()
            ->where('status', AddOnStatus::Active)
            ->whereIn('id', $componentIds)
            ->get();

        if (count($componentIds) !== $components->count()) {
            throw ValidationException::withMessages([
                'custom_component_ids' => ['One or more selected custom options are invalid.'],
            ]);
        }

        $subtotal = (float) ($config->base_fee ?? 0) + (float) $components->sum('price_addition');

        // Custom package duration isn't collected by the Custom Package Calculator
        // (Chapter 6.5) — it only produces a price. Absent an explicit duration
        // component, the photographer's shortest published package duration is
        // used as a reasonable scheduling placeholder; documented as a decision.
        $fallbackDuration = $photographer->packages()->where('status', PackageStatus::Published)->min('duration_minutes') ?? 60;
        $fallbackBuffer = $photographer->packages()->where('status', PackageStatus::Published)->min('buffer_minutes') ?? 0;

        $snapshot = [
            'base_fee' => (string) ($config->base_fee ?? 0),
            'components' => $components->map(fn ($c) => [
                'label' => $c->label,
                'type' => $c->type->value,
                'price_addition' => (string) $c->price_addition,
            ])->values()->toArray(),
        ];

        return [$fallbackDuration + $fallbackBuffer, $subtotal, null, null, $snapshot];
    }

    protected function resolveAddOns(User $photographer, array $addOnIds): array
    {
        if (empty($addOnIds)) {
            return [[], 0.0];
        }

        $addOns = $photographer->addOns()->where('status', AddOnStatus::Active)->whereIn('id', $addOnIds)->get();

        if (count($addOnIds) !== $addOns->count()) {
            throw ValidationException::withMessages([
                'add_on_ids' => ['One or more selected add-ons are invalid or unavailable.'],
            ]);
        }

        $snapshot = $addOns->map(fn ($a) => ['name' => $a->name, 'price' => (string) $a->price])->values()->toArray();

        return [$snapshot, (float) $addOns->sum('price')];
    }

    protected function assertSlotIsAvailable(User $photographer, string $date, string $startTime, int $neededMinutes): void
    {
        $slots = $this->availabilityService->getAvailableStartTimes($photographer, $date, $neededMinutes);

        if (! in_array($startTime, $slots, true)) {
            throw ValidationException::withMessages([
                'start_time' => ['This date and time is not available for booking.'],
            ]);
        }
    }

    protected function assertNoConflict(User $photographer, string $date, string $start, string $end): void
    {
        $conflict = $photographer->bookingsAsPhotographer()
            ->where('event_date', $date)
            ->whereIn('status', [\App\Enums\BookingStatus::Pending, \App\Enums\BookingStatus::Accepted, \App\Enums\BookingStatus::Confirmed])
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'start_time' => ['This time conflicts with another booking request for this photographer.'],
            ]);
        }
    }
}