<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            // Who performed the action (nullable for system-triggered events, e.g. scheduled jobs)
            $table->foreignId('causer_id')->nullable()->constrained('users')->nullOnDelete();

            // What the action was done to (polymorphic: Booking, PhotographerApplication, Payment, User, Review, ...)
            $table->nullableMorphs('subject');

            // Machine-readable action key, e.g. "application.approved", "booking.accepted"
            $table->string('action');

            // Human-readable summary, e.g. "Approved photographer application"
            $table->string('description');

            // Free-form context (old/new values, reasons, amounts, etc.)
            $table->json('metadata')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['causer_id', 'created_at']);
            $table->index(['action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};