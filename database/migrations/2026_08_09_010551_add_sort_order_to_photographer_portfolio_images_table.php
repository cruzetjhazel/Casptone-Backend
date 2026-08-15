<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photographer_portfolio_images', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('status');
        });

        // Backfill: give existing rows a stable order matching their original
        // upload sequence (oldest first), scoped per photographer.
        $rows = DB::table('photographer_portfolio_images')
            ->orderBy('user_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'user_id']);

        $position = [];
        foreach ($rows as $row) {
            $position[$row->user_id] = ($position[$row->user_id] ?? -1) + 1;
            DB::table('photographer_portfolio_images')
                ->where('id', $row->id)
                ->update(['sort_order' => $position[$row->user_id]]);
        }
    }

    public function down(): void
    {
        Schema::table('photographer_portfolio_images', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};