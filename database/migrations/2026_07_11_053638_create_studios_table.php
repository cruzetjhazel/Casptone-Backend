<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('studios', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('location');
        $table->text('description')->nullable();
        $table->string('specialty')->nullable();
        $table->decimal('rating', 2, 1)
              ->default(0);
        $table->string('avatar')
              ->nullable();
        $table->boolean('featured')
              ->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('studios');
    }
};
