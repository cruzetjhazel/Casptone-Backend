<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();

            // client | studio | booking | payment | bug | other — validated against
            // the reporter's own account type in SubmitReportRequest (a client can't
            // target "studio" as themself, etc.)
            $table->string('target_type');

            // Free-text — points at a Booking ID, Transaction/Payment ID, or User ID
            // depending on target_type. Not a foreign key: the referenced entity type
            // varies, and the report must still exist even if that record is later
            // removed.
            $table->string('reference_id')->nullable();

            // Free text, taken directly from the frontend's reason dropdown (including
            // custom "Other" text) — not an enum, since the option set is UI-defined.
            $table->string('reason');

            $table->string('severity');
            $table->text('details');
            $table->string('requested_action');
            $table->string('status')->default('pending');

            // Evidence uploads: [{ "path": "...", "original_name": "...", "mime_type": "..." }]
            $table->json('attachments')->nullable();

            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['reporter_id', 'created_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};