<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * `chk_booking_status` was created before the `expired` status existed
 * (see BookingStatus enum / ExpireStaleBookingHoldsAction), so the raw
 * MySQL CHECK constraint on `bookings.status` never allowed it —
 * any attempt to write status='expired' fails with a 4025 integrity
 * constraint violation even though the app-level enum is fine with it.
 * This adds 'expired' to the allowed list.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT chk_booking_status');

        DB::statement("
            ALTER TABLE bookings
            ADD CONSTRAINT chk_booking_status
            CHECK (`status` in ('pending','accepted','confirmed','rejected','cancelled','completed','expired'))
        ");
    }

    public function down(): void
    {
        // NOTE: rolling back will fail if any row already has
        // status='expired' at that point, since the narrower constraint
        // below can't allow it. Reassign those rows first if you ever
        // need to roll this back on a database with expired bookings.
        DB::statement('ALTER TABLE bookings DROP CONSTRAINT chk_booking_status');

        DB::statement("
            ALTER TABLE bookings
            ADD CONSTRAINT chk_booking_status
            CHECK (`status` in ('pending','accepted','confirmed','rejected','cancelled','completed'))
        ");
    }
};