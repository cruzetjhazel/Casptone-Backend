<?php

namespace Tests\Feature\Notifications;

use App\Enums\PhotographerPaymentReferenceStatus;
use App\Models\Booking;
use App\Models\PhotographerPaymentConfig;
use App\Models\PhotographerPaymentReference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function acceptedBooking(float $totalPrice = 10000): Booking
    {
        $photographer = User::factory()->photographer()->create();
        PhotographerPaymentConfig::factory()->for($photographer)->create();
        $client = User::factory()->create();

        return Booking::factory()->accepted()->create([
            'photographer_id' => $photographer->id,
            'client_id' => $client->id,
            'total_price' => $totalPrice,
        ]);
    }

    protected function registerReference(Booking $booking, string $reference, float $amount): void
    {
        PhotographerPaymentReference::create([
            'photographer_id' => $booking->photographer_id,
            'reference_number' => $reference,
            'amount_received' => $amount,
            'payment_date' => now()->format('Y-m-d'),
            'status' => PhotographerPaymentReferenceStatus::Available,
        ]);
    }

    public function test_matched_payment_notifies_both_parties_and_confirms_booking(): void
    {
        $booking = $this->acceptedBooking(10000);
        $this->registerReference($booking, 'GC-REF-1', 10000);
        Sanctum::actingAs($booking->client);

        $this->postJson("/api/client/bookings/{$booking->id}/payments", [
            'plan' => 'full', 'reference_number' => 'GC-REF-1', 'payer_name' => 'Jane Doe',
            'amount' => 10000, 'payment_date' => now()->format('Y-m-d'),
        ])->assertCreated();

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->client_id, 'type' => \App\Notifications\Payment\PaymentVerifiedNotification::class]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->photographer_id, 'type' => \App\Notifications\Payment\PaymentReceivedNotification::class]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->client_id, 'type' => \App\Notifications\Booking\BookingConfirmedNotification::class]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->photographer_id, 'type' => \App\Notifications\Booking\BookingConfirmedNotification::class]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->client_id, 'type' => \App\Notifications\Payment\FullPaymentCompletedNotification::class]);
    }

    public function test_unmatched_payment_does_not_confirm_booking_and_notifies_pending_verification(): void
    {
        $booking = $this->acceptedBooking(10000);
        Sanctum::actingAs($booking->client);

        $this->postJson("/api/client/bookings/{$booking->id}/payments", [
            'plan' => 'full', 'reference_number' => 'GC-REF-UNKNOWN', 'payer_name' => 'Jane Doe',
            'amount' => 10000, 'payment_date' => now()->format('Y-m-d'),
        ])->assertCreated();

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->client_id, 'type' => \App\Notifications\Payment\PaymentPendingVerificationNotification::class]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->photographer_id, 'type' => \App\Notifications\Payment\PaymentNeedsReviewNotification::class]);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $booking->client_id, 'type' => \App\Notifications\Booking\BookingConfirmedNotification::class]);
    }

    public function test_half_payment_match_notifies_remaining_balance_not_full_payment(): void
    {
        $booking = $this->acceptedBooking(10000);
        $this->registerReference($booking, 'GC-REF-HALF', 5000);
        Sanctum::actingAs($booking->client);

        $this->postJson("/api/client/bookings/{$booking->id}/payments", [
            'plan' => 'half', 'reference_number' => 'GC-REF-HALF', 'payer_name' => 'Jane Doe',
            'amount' => 5000, 'payment_date' => now()->format('Y-m-d'),
        ])->assertCreated();

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->client_id, 'type' => \App\Notifications\Payment\RemainingBalanceNotification::class]);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $booking->client_id, 'type' => \App\Notifications\Payment\FullPaymentCompletedNotification::class]);
    }

    public function test_manual_verification_confirms_booking_and_notifies_client(): void
    {
        $booking = $this->acceptedBooking(10000);
        Sanctum::actingAs($booking->client);
        $response = $this->postJson("/api/client/bookings/{$booking->id}/payments", [
            'plan' => 'full', 'reference_number' => 'GC-REF-MANUAL', 'payer_name' => 'Jane Doe',
            'amount' => 10000, 'payment_date' => now()->format('Y-m-d'),
        ])->assertCreated();

        $paymentId = $response->json('data.id');

        Sanctum::actingAs($booking->photographer);
        $this->postJson("/api/photographer/payments/{$paymentId}/verify")->assertOk();

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->client_id, 'type' => \App\Notifications\Payment\PaymentManuallyVerifiedNotification::class]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->client_id, 'type' => \App\Notifications\Booking\BookingConfirmedNotification::class]);
        $this->assertEquals('confirmed', $booking->fresh()->status->value);
    }

    public function test_payment_rejection_notifies_client_and_does_not_confirm(): void
    {
        $booking = $this->acceptedBooking(10000);
        Sanctum::actingAs($booking->client);
        $response = $this->postJson("/api/client/bookings/{$booking->id}/payments", [
            'plan' => 'full', 'reference_number' => 'GC-REF-REJECT', 'payer_name' => 'Jane Doe',
            'amount' => 10000, 'payment_date' => now()->format('Y-m-d'),
        ])->assertCreated();

        $paymentId = $response->json('data.id');

        Sanctum::actingAs($booking->photographer);
        $this->postJson("/api/photographer/payments/{$paymentId}/reject", ['notes' => 'No funds received.'])->assertOk();

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->client_id, 'type' => \App\Notifications\Payment\PaymentRejectedNotification::class]);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $booking->client_id, 'type' => \App\Notifications\Booking\BookingConfirmedNotification::class]);
    }

    public function test_onsite_payment_notifies_full_payment_completed(): void
    {
        $booking = $this->acceptedBooking(10000);
        $booking->update(['payment_plan' => 'half', 'payment_status' => 'partially_paid']);
        \App\Models\Payment::create([
            'booking_id' => $booking->id, 'client_id' => $booking->client_id, 'photographer_id' => $booking->photographer_id,
            'type' => 'online', 'method' => 'gcash', 'plan' => 'half', 'amount' => 5000,
            'reference_number' => 'GC-PRE', 'payer_name' => 'Jane Doe', 'payment_date' => now()->format('Y-m-d'),
            'matching_status' => 'matched',
        ]);
        $booking->update(['status' => 'confirmed']);

        Sanctum::actingAs($booking->photographer);
        $this->postJson("/api/photographer/bookings/{$booking->id}/payments/onsite", [
            'amount' => 5000, 'payment_date' => now()->format('Y-m-d'),
        ])->assertOk();

        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->client_id, 'type' => \App\Notifications\Payment\OnsitePaymentRecordedNotification::class]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->client_id, 'type' => \App\Notifications\Payment\FullPaymentCompletedNotification::class]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $booking->photographer_id, 'type' => \App\Notifications\Payment\FullPaymentCompletedNotification::class]);
    }
}