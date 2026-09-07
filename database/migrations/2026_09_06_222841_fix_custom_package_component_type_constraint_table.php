<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The original create_custom_package_components_table migration's MySQL
 * CHECK constraint only allowed type IN ('flat_option','photo_count_tier',
 * 'delivery_duration_tier'). App\Enums\CustomPackageComponentType and
 * CustomPackageComponentRequest have since standardized on a generic
 * ('flat_option','tier_option') pair — tier_name carries the actual tier
 * label (e.g. "Edited Photos", "Coverage Duration") instead of baking
 * specific tier names into the type itself. The 2026_09_06_211623 migration
 * added duration_minutes/buffer_minutes but never corrected this constraint,
 * so every 'tier_option' insert — including coverage-duration tiers —
 * violates it on MySQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE custom_package_components DROP CONSTRAINT chk_component_type');
        DB::statement(
            "ALTER TABLE custom_package_components ADD CONSTRAINT chk_component_type
             CHECK (type IN ('flat_option','tier_option'))"
        );
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE custom_package_components DROP CONSTRAINT chk_component_type');
        DB::statement(
            "ALTER TABLE custom_package_components ADD CONSTRAINT chk_component_type
             CHECK (type IN ('flat_option','photo_count_tier','delivery_duration_tier'))"
        );
    }
};