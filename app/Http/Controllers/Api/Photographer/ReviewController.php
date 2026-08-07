<?php

namespace App\Http\Controllers\Api\Photographer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReplyReviewRequest;
use App\Http\Requests\ReportReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Traits\ApiResponses;

class ReviewController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $reviews = Review::where('photographer_id', auth()->id())
            ->with(['booking', 'client', 'photographer'])
            ->latest()
            ->get();

        return $this->success(ReviewResource::collection($reviews));
    }

    public function reply(ReplyReviewRequest $request, Review $review)
    {
        $this->authorize('reply', $review);

        $review->update([
            'reply' => $request->validated('reply'),
            'replied_at' => now(),
        ]);

        $review->load(['booking', 'client', 'photographer']);

        return $this->success(new ReviewResource($review), 'Reply published.');
    }

    public function report(ReportReviewRequest $request, Review $review)
    {
        $this->authorize('report', $review);

        $review->update([
            'report_reason' => $request->validated('reason'),
            'reported_at' => now(),
        ]);

        $review->load(['booking', 'client', 'photographer']);

        return $this->success(new ReviewResource($review), 'Review reported to administrators.');
    }
}