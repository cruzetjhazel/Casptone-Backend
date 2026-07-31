<?php

namespace App\Actions\Review;

use App\Models\Review;
use Illuminate\Validation\ValidationException;

class ReplyToReviewAction
{
    public function execute(Review $review, string $reply): Review
    {
        if ($review->hasReply()) {
            throw ValidationException::withMessages([
                'reply' => ['You have already replied to this review. Replies cannot be edited.'],
            ]);
        }

        $review->reply = $reply;
        $review->replied_at = now();
        $review->save();

        return $review->fresh();
    }
}