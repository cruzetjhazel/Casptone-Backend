<?php

namespace App\Http\Controllers\Api\Photographer;

use App\Actions\Payment\InvalidatePaymentReferenceAction;
use App\Actions\Payment\RegisterPaymentReferenceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterPaymentReferenceRequest;
use App\Http\Resources\PhotographerPaymentReferenceResource;
use App\Models\PhotographerPaymentReference;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class PaymentReferenceController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        $this->authorize('create', PhotographerPaymentReference::class);

        return $this->success(
            PhotographerPaymentReferenceResource::collection(
                $request->user()->paymentReferences()->latest()->get()
            )
        );
    }

    public function store(RegisterPaymentReferenceRequest $request, RegisterPaymentReferenceAction $action)
    {
        $this->authorize('create', PhotographerPaymentReference::class);

        $reference = $action->execute($request->user(), $request->validated());

        return $this->success(new PhotographerPaymentReferenceResource($reference), 'Payment reference recorded.', 201);
    }

    public function invalidate(PhotographerPaymentReference $paymentReference, InvalidatePaymentReferenceAction $action)
    {
        $this->authorize('invalidate', $paymentReference);

        $reference = $action->execute($paymentReference);

        return $this->success(new PhotographerPaymentReferenceResource($reference), 'Payment reference invalidated.');
    }
}