<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotographerPaymentConfig extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'gcash_account_name', 'gcash_account_number', 'gcash_qr_path'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}