<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentIntent extends Model
{
    protected $table = 'tnmshop_payment_intents';

    protected $fillable = [
        'intent_id',
        'order_id',
        'status',
        'amount',
        'currency',
        'return_url'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
