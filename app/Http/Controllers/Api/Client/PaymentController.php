<?php

namespace App\Http\Controllers\Api\Client;

use App\Actions\Payment\SubmitPaymentAction;
use App\Enums\PaymentPlan;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        return $this->success(
            PaymentResource::collection($request->user()->paymentsAsClient()->latest()->get())
        );
    }

    public function paymentInfo(Request $request, Booking $booking)
    {
        $this->authorizeOwnership($request, $booking);

        $config = $booking->photographer->paymentConfig;

        if (! $config) {
            throw ValidationException::withMessages([
                'booking' => ['This photographer has not set up their GCash payment details yet.'],
            ]);
        }

        return $this->success([
            'gcash' => [
                'account_name' => $config->gcash_account_name,
                'account_number' => $config->gcash_account_number,
                'qr_url' => \Illuminate\Support\Facades\Storage::disk('public')->url($config->gcash_qr_path),
            ],
            'amounts' => [
                'total_price' => $booking->total_price,
                'full_payment_amount' => $booking->onlineAmountDueFor(PaymentPlan::Full),
                'half_payment_amount' => $booking->onlineAmountDueFor(PaymentPlan::Half),
            ],
            'booking_status' => $booking->status->value,
            'payment_status' => $booking->payment_status->value,
        ]);
    }

    public function store(SubmitPaymentRequest $request, Booking $booking, SubmitPaymentAction $action)
    {
        $this->authorizeOwnership($request, $booking);

        $payment = $action->execute($booking, $request->validated());

        return $this->success(new PaymentResource($payment), 'Payment submitted. Booking is now confirmed.', 201);
    }

    protected function authorizeOwnership(Request $request, Booking $booking): void
    {
        abort_unless($request->user()->isClient(), 403);
        abort_unless($booking->client_id === $request->user()->id, 403);
    }
}