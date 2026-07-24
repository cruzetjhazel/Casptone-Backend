<?php

use App\Enums\PortfolioImageStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photographer_portfolio_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('status', 20)->default(PortfolioImageStatus::Active->value)->index();
            $table->timestamps();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE photographer_portfolio_images ADD CONSTRAINT chk_portfolio_status
                 CHECK (status IN ('active','archived'))"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('photographer_portfolio_images');
    }
};