<?php

use App\Enums\PhotographerApplicationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photographer_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('photographer_type', 20);
            $table->string('status', 30)->default(PhotographerApplicationStatus::Draft->value)->index();

            // Photographer / Studio information (Chapter 5.2 / 5.3)
            $table->string('business_name')->nullable();
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('years_active')->nullable();
            $table->unsignedSmallInteger('team_size')->nullable();

            // Service information
            $table->json('services')->nullable();
            $table->string('other_services')->nullable();
            $table->string('coverage_area', 40)->nullable();
            $table->json('shooting_types')->nullable();
            $table->decimal('price_min', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable();

            // Verification documents (Chapter 5.4) — private, never publicly displayed
            $table->string('government_id_path')->nullable();
            $table->string('selfie_with_id_path')->nullable();
            $table->string('business_permit_path')->nullable();
            $table->json('additional_document_paths')->nullable();

            // Review / audit metadata
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->text('revision_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('can_reapply')->default(true);

            $table->timestamps();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE photographer_applications ADD CONSTRAINT chk_prof_app_type
                 CHECK (photographer_type IN ('freelancer','studio'))"
            );
            DB::statement(
                "ALTER TABLE photographer_applications ADD CONSTRAINT chk_prof_app_status
                 CHECK (status IN ('draft','pending_review','revision_requested','approved','rejected'))"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('photographer_applications');
    }
};