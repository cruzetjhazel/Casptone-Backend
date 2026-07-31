<?php

namespace App\Http\Controllers\Api\Client;

use App\Actions\Review\SubmitReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Booking;
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
                    ->where('client_id', $request->user()->id)
                    ->latest()
                    ->get()
            )
        );
    }

    public function store(CreateReviewRequest $request, SubmitReviewAction $action)
    {
        $this->authorize('create', Review::class);

        $booking = Booking::findOrFail($request->validated('booking_id'));
        abort_unless($booking->client_id === $request->user()->id, 403, 'You can only review your own bookings.');

        $review = $action->execute($booking, $request->validated());

        return $this->success(new ReviewResource($review->load(['client', 'photographer', 'booking'])), 'Review submitted.', 201);
    }
}