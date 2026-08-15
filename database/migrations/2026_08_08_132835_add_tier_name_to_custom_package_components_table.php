<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('custom_package_components', 'tier_name')) {
            Schema::table('custom_package_components', function (Blueprint $table) {
                $table->string('tier_name')->nullable()->after('type');
            });
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE custom_package_components DROP CONSTRAINT chk_component_type');
            DB::statement(
                "ALTER TABLE custom_package_components ADD CONSTRAINT chk_component_type
                 CHECK (type IN ('flat_option','tier_option'))"
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('custom_package_components', 'tier_name')) {
            Schema::table('custom_package_components', function (Blueprint $table) {
                $table->dropColumn('tier_name');
            });
        }

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE custom_package_components DROP CONSTRAINT chk_component_type');
            DB::statement(
                "ALTER TABLE custom_package_components ADD CONSTRAINT chk_component_type
                 CHECK (type IN ('flat_option','photo_count_tier','delivery_duration_tier','photographer_count_tier'))"
            );
        }
    }
};