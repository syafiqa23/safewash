<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'laundry_order_id',
        'provider',
        'gateway_name',
        'payment_method',
        'reference',
        'external_id',
        'checkout_url',
        'checkout_token',
        'gross_amount',
        'gateway_fee',
        'net_amount',
        'status',
        'webhook_status',
        'paid_at',
        'expired_at',
        'gateway_payload',
    ];

    protected function casts(): array
    {
        return [
            'gross_amount' => 'decimal:2',
            'gateway_fee' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expired_at' => 'datetime',
            'gateway_payload' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class, 'laundry_order_id');
    }
}
