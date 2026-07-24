<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('photographer_id')->constrained('users')->cascadeOnDelete();

            $table->string('type', 10); // online (GCash, client-submitted) | onsite (photographer-recorded)
            $table->string('method', 20)->default('gcash');
            $table->string('plan', 10); // half | full — the booking's payment plan this payment counts toward

            $table->decimal('amount', 10, 2);
            $table->string('reference_number')->nullable()->unique(); // GCash reference; null for onsite payments
            $table->date('payment_date');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['booking_id']);
            $table->index(['client_id']);
            $table->index(['photographer_id']);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE payments ADD CONSTRAINT chk_payment_type
                 CHECK (type IN ('online','onsite'))"
            );
            DB::statement(
                "ALTER TABLE payments ADD CONSTRAINT chk_payment_plan
                 CHECK (plan IN ('half','full'))"
            );
            DB::statement('ALTER TABLE payments ADD CONSTRAINT chk_payment_amount CHECK (amount >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};