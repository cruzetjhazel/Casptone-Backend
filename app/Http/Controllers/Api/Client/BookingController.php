<?php

namespace App\Http\Controllers\Api\Client;

use App\Actions\Booking\CreateBookingAction;
use App\Actions\Booking\RequestBookingCancellationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBookingRequest;
use App\Http\Requests\RequestBookingCancellationRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        return $this->success(
            BookingResource::collection($request->user()->bookingsAsClient()->latest()->get())
        );
    }

    public function store(CreateBookingRequest $request, CreateBookingAction $action)
    {
        $this->authorize('create', Booking::class);

        $booking = $action->execute($request->user(), $request->validated());

        return $this->success(new BookingResource($booking), 'Booking request submitted.', 201);
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        return $this->success(new BookingResource($booking));
    }

    public function requestCancellation(RequestBookingCancellationRequest $request, Booking $booking, RequestBookingCancellationAction $action)
    {
        $this->authorize('requestCancellation', $booking);

        $booking = $action->execute($booking, $request->validated('reason'));

        return $this->success(new BookingResource($booking), 'Cancellation requested.');
    }
}