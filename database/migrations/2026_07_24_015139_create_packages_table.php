<?php

use App\Enums\PackageStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('included_items')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('duration_minutes');
            $table->unsignedInteger('buffer_minutes')->default(0);
            $table->string('status', 20)->default(PackageStatus::Draft->value)->index();
            $table->timestamps();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE packages ADD CONSTRAINT chk_package_status
                 CHECK (status IN ('draft','published','archived'))"
            );
            DB::statement('ALTER TABLE packages ADD CONSTRAINT chk_package_price CHECK (price >= 0)');
            DB::statement('ALTER TABLE packages ADD CONSTRAINT chk_package_duration CHECK (duration_minutes > 0)');
            DB::statement('ALTER TABLE packages ADD CONSTRAINT chk_package_buffer CHECK (buffer_minutes >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};