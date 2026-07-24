<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bookings DROP CONSTRAINT chk_booking_payment_status');
            DB::statement(
                "ALTER TABLE bookings ADD CONSTRAINT chk_booking_payment_status
                 CHECK (payment_status IN ('pending','pending_verification','partially_paid','fully_paid','failed','cancelled'))"
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bookings DROP CONSTRAINT chk_booking_payment_status');
            DB::statement(
                "ALTER TABLE bookings ADD CONSTRAINT chk_booking_payment_status
                 CHECK (payment_status IN ('pending','partially_paid','fully_paid','failed','cancelled'))"
            );
        }
    }
};