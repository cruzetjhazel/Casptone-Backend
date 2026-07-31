<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Null until the booking is Confirmed and the photographer starts
            // updating it — see Booking::canManageServiceTracker().
            $table->string('service_status', 20)->nullable()->after('status');
            $table->timestamp('service_status_updated_at')->nullable()->after('service_status');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE bookings ADD CONSTRAINT chk_booking_service_status
                 CHECK (service_status IS NULL OR service_status IN
                 ('upcoming','event_day','in_progress','photo_editing','ready_for_release','completed'))"
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bookings DROP CONSTRAINT chk_booking_service_status');
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['service_status', 'service_status_updated_at']);
        });
    }
};