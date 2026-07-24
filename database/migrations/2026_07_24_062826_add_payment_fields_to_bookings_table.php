<?php

use App\Enums\BookingPaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_plan', 10)->nullable()->after('total_price');
            $table->string('payment_status', 20)
                ->default(BookingPaymentStatus::Pending->value)
                ->after('payment_plan')
                ->index();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE bookings ADD CONSTRAINT chk_booking_payment_plan
                 CHECK (payment_plan IS NULL OR payment_plan IN ('half','full'))"
            );
            DB::statement(
                "ALTER TABLE bookings ADD CONSTRAINT chk_booking_payment_status
                 CHECK (payment_status IN ('pending','partially_paid','fully_paid','failed','cancelled'))"
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bookings DROP CONSTRAINT chk_booking_payment_plan');
            DB::statement('ALTER TABLE bookings DROP CONSTRAINT chk_booking_payment_status');
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_plan', 'payment_status']);
        });
    }
};