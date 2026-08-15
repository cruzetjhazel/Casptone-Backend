<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// NOTE: table names assumed from Laravel/model conventions — verify against
// your actual schema before running:
//   Package               -> packages
//   AddOn ($table)         -> add_ons        (confirmed from AddOn.php)
//   WalkInClient           -> walk_in_clients
//   PhotographerPortfolioImage -> photographer_portfolio_images
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('status');
        });

        Schema::table('add_ons', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('status');
        });

        Schema::table('walk_in_clients', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('status');
        });

        Schema::table('photographer_portfolio_images', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('packages', fn (Blueprint $table) => $table->dropColumn('archived_at'));
        Schema::table('add_ons', fn (Blueprint $table) => $table->dropColumn('archived_at'));
        Schema::table('walk_in_clients', fn (Blueprint $table) => $table->dropColumn('archived_at'));
        Schema::table('photographer_portfolio_images', fn (Blueprint $table) => $table->dropColumn('archived_at'));
    }
};