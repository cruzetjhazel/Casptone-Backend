<?php

namespace App\Http\Controllers\Api\Photographer;

use App\Actions\Review\ReplyToReviewAction;
use App\Actions\Review\ReportReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReplyToReviewRequest;
use App\Http\Requests\ReportReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        return $this->success(
            ReviewResource::collection(
                Review::with(['client', 'photographer', 'booking'])
                    ->where('photographer_id', $request->user()->id)
                    ->latest()
                    ->get()
            )
        );
    }

    public function reply(ReplyToReviewRequest $request, Review $review, ReplyToReviewAction $action)
    {
        $this->authorize('reply', $review);

        $review = $action->execute($review, $request->validated('reply'));

        return $this->success(new ReviewResource($review->load(['client', 'photographer', 'booking'])), 'Reply published.');
    }

    public function report(ReportReviewRequest $request, Review $review, ReportReviewAction $action)
    {
        $this->authorize('report', $review);

        $review = $action->execute($review, $request->validated('reason'));

        return $this->success(new ReviewResource($review->load(['client', 'photographer', 'booking'])), 'Review reported to administrators.');
    }

    // Deliberately no hide/archive/destroy method here — see ReviewPolicy.
}