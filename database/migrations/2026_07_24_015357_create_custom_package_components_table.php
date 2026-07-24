<?php

use App\Enums\AddOnStatus;
use App\Enums\CustomPackageComponentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_package_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('label');
            $table->decimal('price_addition', 10, 2);
            $table->string('status', 20)->default(AddOnStatus::Active->value)->index();
            $table->timestamps();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE custom_package_components ADD CONSTRAINT chk_component_type
                 CHECK (type IN ('flat_option','photo_count_tier','delivery_duration_tier'))"
            );
            DB::statement(
                "ALTER TABLE custom_package_components ADD CONSTRAINT chk_component_status
                 CHECK (status IN ('active','archived'))"
            );
            DB::statement('ALTER TABLE custom_package_components ADD CONSTRAINT chk_component_price CHECK (price_addition >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_package_components');
    }
};