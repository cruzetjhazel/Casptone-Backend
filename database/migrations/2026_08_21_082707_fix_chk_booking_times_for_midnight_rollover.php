<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            // The original constraint (end_time > start_time) rejects any
            // booking that crosses midnight, e.g. 23:00 -> 00:00, because
            // TIME columns have no date component and 00:00:00 is not
            // "greater than" 23:00:00.
            //
            // Bookings that end exactly at midnight are a valid same-night
            // session ending at the start of the next calendar day, so we
            // allow end_time = '00:00:00' as an explicit exception.
            DB::statement('ALTER TABLE bookings DROP CONSTRAINT chk_booking_times');
            DB::statement(
                "ALTER TABLE bookings ADD CONSTRAINT chk_booking_times
                 CHECK (end_time > start_time OR end_time = '00:00:00')"
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bookings DROP CONSTRAINT chk_booking_times');
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT chk_booking_times CHECK (end_time > start_time)');
        }
    }
};