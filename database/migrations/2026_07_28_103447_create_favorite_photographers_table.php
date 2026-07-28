<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorite_photographers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('photographer_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['client_id', 'photographer_id'], 'client_photographer_favorite_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorite_photographers');
    }
};