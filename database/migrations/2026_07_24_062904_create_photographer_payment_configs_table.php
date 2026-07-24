<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photographer_payment_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('gcash_account_name');
            $table->string('gcash_account_number', 20);
            $table->string('gcash_qr_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photographer_payment_configs');
    }
};