<?php

namespace App\Http\Controllers\Api\Photographer;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWalkInClientRequest;
use App\Http\Requests\UpdateWalkInClientRequest;
use App\Http\Resources\WalkInClientResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\WalkInClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Merged Client list.
     *
     * Rule 46 — Registered Clients automatically appear here when they have
     * a booking relationship with this Photographer (derived from Booking,
     * no stored record).
     * Rules 47-48 — Walk-in Clients are separate manually-entered records
     * that never get a platform account.
     */
    public function index(Request $request): JsonResponse
    {
        $photographerId = $request->user()->id;

        $registered = Booking::query()
            ->where('photographer_id', $photographerId)
            ->with('client.clientProfile')
            ->get()
            ->groupBy('client_id')
            ->map(function ($clientBookings) use ($photographerId) {
                $client = $clientBookings->first()->client;

                $hasActiveBooking = $clientBookings->contains(
                    fn ($booking) => in_array($booking->status, [
                        BookingStatus::Pending,
                        BookingStatus::Accepted,
                        BookingStatus::Confirmed,
                    ], true)
                );

                $spent = (float) Payment::query()
                    ->where('client_id', $client->id)
                    ->where('photographer_id', $photographerId)
                    ->sum('amount');

                return [
                    'id' => 'R-' . $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone_number,
                    'location' => $client->clientProfile?->address,
                    'bookings' => $clientBookings->count(),
                    'spent' => $spent,
                    'type' => 'registered',
                    'status' => $hasActiveBooking ? 'active' : 'inactive',
                    'source' => 'Platform',
                    'joined_year' => (int) $client->created_at->format('Y'),
                ];
            })
            ->values();

        $walkIns = WalkInClient::query()
            ->where('photographer_id', $photographerId)
            ->where('status', '!=', 'archived')
            ->latest()
            ->get();

        $walkInsFormatted = collect(WalkInClientResource::collection($walkIns)->resolve());

        return response()->json([
            'data' => $registered->concat($walkInsFormatted)->values(),
        ]);
    }

    public function store(StoreWalkInClientRequest $request): WalkInClientResource
    {
        $client = WalkInClient::create([
            ...$request->validated(),
            'photographer_id' => $request->user()->id,
            'status' => 'inactive',
        ]);

        return new WalkInClientResource($client);
    }

    public function update(UpdateWalkInClientRequest $request, WalkInClient $walkInClient): WalkInClientResource
    {
        abort_unless($walkInClient->photographer_id === $request->user()->id, 403);

        $walkInClient->update($request->validated());

        return new WalkInClientResource($walkInClient);
    }

    public function archive(Request $request, WalkInClient $walkInClient): WalkInClientResource
    {
        abort_unless($walkInClient->photographer_id === $request->user()->id, 403);

        $walkInClient->update(['status' => 'archived']);

        return new WalkInClientResource($walkInClient);
    }
}