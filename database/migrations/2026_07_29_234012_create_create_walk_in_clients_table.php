<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('walk_in_clients', function (Blueprint $table) {
            $table->id();
            // The Photographer (Freelancer or Studio) who manually recorded this client.
            // Rule 47: Photographers can manually add separate Walk-in Client records
            // for people who booked outside the platform.
            $table->foreignId('photographer_id')->constrained('users')->cascadeOnDelete();

            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('location')->nullable();

            // Matches the lead-source options already in the StudioClients.tsx mock.
            $table->enum('source', [
                'facebook',
                'messenger',
                'phone_call',
                'walk_in',
                'referral',
            ])->default('walk_in');

            // Rule 48: Walk-in Clients do not automatically receive a platform account,
            // so there is no user_id / account link here.
            $table->enum('status', ['active', 'inactive', 'archived'])->default('inactive');

            $table->timestamps();

            $table->index(['photographer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('walk_in_clients');
    }
};