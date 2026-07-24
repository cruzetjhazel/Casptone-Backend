<?php

use App\Enums\PaymentMatchingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payer_name')->nullable()->after('reference_number');
            $table->foreignId('photographer_payment_reference_id')->nullable()
                ->after('payer_name')
                ->constrained('photographer_payment_references')
                ->nullOnDelete();
            $table->string('matching_status', 20)
                ->default(PaymentMatchingStatus::Submitted->value)
                ->after('photographer_payment_reference_id')
                ->index();
            $table->foreignId('verified_by')->nullable()->after('matching_status')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->string('verification_action', 20)->nullable()->after('verified_at');
            $table->text('verification_notes')->nullable()->after('verification_action');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE payments ADD CONSTRAINT chk_payment_matching_status
                 CHECK (matching_status IN ('submitted','pending_match','matched','not_matched','manually_verified','rejected'))"
            );
            DB::statement(
                "ALTER TABLE payments ADD CONSTRAINT chk_payment_verification_action
                 CHECK (verification_action IS NULL OR verification_action IN ('verified','rejected'))"
            );
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE payments DROP CONSTRAINT chk_payment_matching_status');
            DB::statement('ALTER TABLE payments DROP CONSTRAINT chk_payment_verification_action');
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('photographer_payment_reference_id');
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['payer_name', 'matching_status', 'verification_action', 'verification_notes', 'verified_at']);
        });
    }
};