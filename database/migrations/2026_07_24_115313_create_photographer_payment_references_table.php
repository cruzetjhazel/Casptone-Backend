<?php

use App\Enums\PaymentType;
use App\Enums\PhotographerPaymentReferenceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photographer_payment_references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photographer_id')->constrained('users')->cascadeOnDelete();
            $table->string('reference_number');
            $table->decimal('amount_received', 10, 2);
            $table->date('payment_date');
            $table->string('payment_type', 10)->default(PaymentType::Online->value);
            $table->string('status', 20)->default(PhotographerPaymentReferenceStatus::Available->value)->index();
            $table->timestamps();
            $table->unique(['photographer_id', 'reference_number'], 'ppr_photographer_reference_unique');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE photographer_payment_references ADD CONSTRAINT chk_ppr_payment_type
                 CHECK (payment_type IN ('online','onsite'))"
            );
            DB::statement(
                "ALTER TABLE photographer_payment_references ADD CONSTRAINT chk_ppr_status
                 CHECK (status IN ('available','matched','used','invalidated'))"
            );
            DB::statement('ALTER TABLE photographer_payment_references ADD CONSTRAINT chk_ppr_amount CHECK (amount_received >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('photographer_payment_references');
    }
};