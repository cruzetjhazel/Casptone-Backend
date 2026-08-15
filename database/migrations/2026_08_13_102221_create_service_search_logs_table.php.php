<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_search_logs', function (Blueprint $table) {
            $table->id();
            $table->string('term', 100);
            $table->timestamp('created_at')->useCurrent();

            $table->index('term');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_search_logs');
    }
};