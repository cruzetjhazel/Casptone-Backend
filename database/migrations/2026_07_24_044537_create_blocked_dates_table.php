<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                'ALTER TABLE blocked_dates ADD CONSTRAINT chk_blocked_date_times
                 CHECK (
                     (start_time IS NULL AND end_time IS NULL)
                     OR (start_time IS NOT NULL AND end_time IS NOT NULL AND end_time > start_time)
                 )'
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_dates');
    }
};