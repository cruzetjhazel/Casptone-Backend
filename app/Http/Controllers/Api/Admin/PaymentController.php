<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponses;

    public function index(Request $request)
    {
        abort_unless($request->user()->isAdministrator(), 403);

        return $this->success(
            PaymentResource::collection(Payment::latest()->get())
        );
    }
}