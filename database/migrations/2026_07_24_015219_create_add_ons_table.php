<?php

use App\Enums\AddOnStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('add_ons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('status', 20)->default(AddOnStatus::Active->value)->index();
            $table->timestamps();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE add_ons ADD CONSTRAINT chk_addon_status
                 CHECK (status IN ('active','archived'))"
            );
            DB::statement('ALTER TABLE add_ons ADD CONSTRAINT chk_addon_price CHECK (price >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('add_ons');
    }
};