<?php

use App\Enums\BookingLocationType;
use App\Enums\BookingStatus;
use App\Enums\CancellationDecision;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('photographer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();

            $table->boolean('is_custom_package')->default(false);
            $table->json('package_snapshot')->nullable();
            $table->json('custom_package_snapshot')->nullable();
            $table->json('add_ons_snapshot')->nullable();

            $table->string('event_type', 40);
            $table->string('custom_event_type')->nullable();
            $table->date('event_date');
            $table->time('start_time');
            $table->time('end_time');

            $table->string('location_type', 30);
            $table->string('event_address')->nullable();
            $table->unsignedInteger('guest_count')->nullable();
            $table->text('special_requests')->nullable();

            $table->decimal('subtotal', 10, 2);
            $table->decimal('total_price', 10, 2);

            $table->string('status', 20)->default(BookingStatus::Pending->value)->index();
            $table->timestamp('hold_expires_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->string('cancellation_decision', 20)->nullable();
            $table->timestamp('cancellation_decided_at')->nullable();

            $table->timestamps();

            $table->index(['photographer_id', 'event_date']);
            $table->index(['client_id', 'event_date']);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE bookings ADD CONSTRAINT chk_booking_status
                 CHECK (status IN ('pending','accepted','confirmed','rejected','cancelled','completed'))"
            );
            DB::statement(
                "ALTER TABLE bookings ADD CONSTRAINT chk_booking_location_type
                 CHECK (location_type IN ('studio','client_location','outdoor_location','other'))"
            );
            DB::statement(
                "ALTER TABLE bookings ADD CONSTRAINT chk_booking_cancellation_decision
                 CHECK (cancellation_decision IS NULL OR cancellation_decision IN ('approved','rejected'))"
            );
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT chk_booking_times CHECK (end_time > start_time)');
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT chk_booking_prices CHECK (subtotal >= 0 AND total_price >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};