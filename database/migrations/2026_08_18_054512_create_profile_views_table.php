<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photographer_id')->constrained('users')->cascadeOnDelete();

            // sha256 of "user:{id}" for logged-in visitors, or "ip:{ip}|ua:{user agent}"
            // for anonymous ones — never store the raw IP/user agent.
            $table->string('viewer_hash', 64);

            // Calendar date the view happened on, used for the once-per-day dedupe.
            $table->date('viewed_on');

            $table->timestamp('created_at')->useCurrent();

            // One row per photographer/visitor/day — re-visits the same day are ignored.
            $table->unique(['photographer_id', 'viewer_hash', 'viewed_on']);
            $table->index(['photographer_id', 'viewed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_views');
    }
};