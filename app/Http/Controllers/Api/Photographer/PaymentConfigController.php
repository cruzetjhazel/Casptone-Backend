<?php

namespace App\Http\Controllers\Api\Photographer;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePaymentConfigRequest;
use App\Http\Resources\PaymentConfigResource;
use App\Models\PhotographerPaymentConfig;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PaymentConfigController extends Controller
{
    use ApiResponses;

    public function show(Request $request)
    {
        abort_unless($request->user()->isPhotographer(), 403);

        $config = PhotographerPaymentConfig::where('user_id', $booking->photographer_id)->first();

        return $this->success($config ? new PaymentConfigResource($config) : null);
    }

    public function store(UpdatePaymentConfigRequest $request)
    {
        $photographer = $request->user();

        abort_unless($photographer->isPhotographer(), 403);

        if (! $photographer->isApprovedPhotographer()) {
            throw ValidationException::withMessages([
                'photographer' => ['You must be an approved photographer to manage payment settings.'],
            ]);
        }

        $data = $request->validated();

        // Query directly instead of the `paymentConfig` relation accessor: the same
        // $photographer instance can be reused across requests (e.g. Sanctum::actingAs
        // in tests), and Eloquent caches a lazy-loaded relation on first access — so a
        // stale "no config yet" null would stick even after this method creates one.
        $existing = PhotographerPaymentConfig::where('user_id', $photographer->id)->first();

        if (! $existing && ! $request->hasFile('gcash_qr_code')) {
            throw ValidationException::withMessages([
                'gcash_qr_code' => ['A GCash QR code image is required.'],
            ]);
        }

        if ($request->hasFile('gcash_qr_code')) {
            if ($existing) {
                Storage::disk('public')->delete($existing->gcash_qr_path);
            }
            $data['gcash_qr_path'] = $request->file('gcash_qr_code')->store('gcash-qr', 'public');
        }

        unset($data['gcash_qr_code']);

        $config = PhotographerPaymentConfig::updateOrCreate(
            ['user_id' => $photographer->id],
            $data
        );

        return $this->success(new PaymentConfigResource($config), 'Payment settings saved.');
    }
}