<?php

namespace App\Http\Controllers\Api\Photographer;

use App\Actions\Payment\RecordOnsitePaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\RecordOnsitePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use App\Models\PhotographerPaymentConfig; 

class PaymentController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        return $this->success(
            PaymentResource::collection($request->user()->paymentsAsPhotographer()->latest()->get())
        );
    }

    public function storeOnsite(RecordOnsitePaymentRequest $request, Booking $booking, RecordOnsitePaymentAction $action)
    {
        abort_unless($request->user()->isPhotographer(), 403);
        abort_unless($booking->photographer_id === $request->user()->id, 403);

        $payment = $action->execute($booking, $request->validated());

        return $this->success(new PaymentResource($payment), 'Onsite payment recorded.');
    }
}