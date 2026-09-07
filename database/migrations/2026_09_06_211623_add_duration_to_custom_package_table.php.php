<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nullable — only set on the specific component(s) meant to represent
        // a selectable photography coverage duration (e.g. a "Coverage
        // Duration" tier with options like "2 hours" = 120). Every other
        // component (photo tiers, delivery tiers, flat add-ons, etc.) leaves
        // this null. CreateBookingAction::resolveCustomPackage() uses the
        // presence of a non-null value — among the client's selected
        // components — to identify their duration choice; it does not rely
        // on `type` or `tier_name` string matching.
        Schema::table('custom_package_components', function (Blueprint $table) {
            $table->unsignedInteger('duration_minutes')->nullable()->after('price_addition');
        });

        // One buffer value per photographer, applied to every custom-package
        // booking they receive — mirrors Package.buffer_minutes, which is
        // per fixed package instead. Custom packages have no per-package row
        // to attach a buffer to, so it lives on the photographer's single
        // custom_package_configs row.
        Schema::table('custom_package_configs', function (Blueprint $table) {
            $table->unsignedInteger('buffer_minutes')->default(0)->after('base_fee');
        });
    }

    public function down(): void
    {
        Schema::table('custom_package_components', function (Blueprint $table) {
            $table->dropColumn('duration_minutes');
        });

        Schema::table('custom_package_configs', function (Blueprint $table) {
            $table->dropColumn('buffer_minutes');
        });
    }
};