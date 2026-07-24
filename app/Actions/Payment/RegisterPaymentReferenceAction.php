<?php

namespace App\Actions\Payment;

use App\Enums\PhotographerPaymentReferenceStatus;
use App\Models\PhotographerPaymentReference;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class RegisterPaymentReferenceAction
{
    public function execute(User $photographer, array $data): PhotographerPaymentReference
    {
        $exists = PhotographerPaymentReference::where('photographer_id', $photographer->id)
            ->where('reference_number', $data['reference_number'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'reference_number' => ['You have already recorded this reference number.'],
            ]);
        }

        return PhotographerPaymentReference::create([
            'photographer_id' => $photographer->id,
            'reference_number' => $data['reference_number'],
            'amount_received' => $data['amount_received'],
            'payment_date' => $data['payment_date'],
            'status' => PhotographerPaymentReferenceStatus::Available,
        ]);
    }
}