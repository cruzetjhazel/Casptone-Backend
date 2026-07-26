<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photographer_profiles', function (Blueprint $table) {
            $table->json('style')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('photographer_profiles', function (Blueprint $table) {
            $table->string('style')->nullable()->change();
        });
    }
};