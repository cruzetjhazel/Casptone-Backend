<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // One review per booking — enforced by the unique() below AND by
            // SubmitReviewAction checking for an existing review first.
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('photographer_id')->constrained('users')->cascadeOnDelete();

            $table->unsignedTinyInteger('rating');
            $table->text('comment'); // text only — no photo/attachment column by design

            // Photographer's one official reply. Nullable until replied,
            // then immutable — enforced in ReplyToReviewAction, not here.
            $table->text('reply')->nullable();
            $table->timestamp('replied_at')->nullable();

            // Photographers can report a review to admins but can NEVER hide
            // or delete it — there is deliberately no `hidden`/`deleted` flag
            // on this table.
            $table->text('report_reason')->nullable();
            $table->timestamp('reported_at')->nullable();

            $table->timestamps();

            $table->unique('booking_id');
            $table->index('photographer_id');
            $table->index('client_id');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE reviews ADD CONSTRAINT chk_review_rating CHECK (rating BETWEEN 1 AND 5)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};