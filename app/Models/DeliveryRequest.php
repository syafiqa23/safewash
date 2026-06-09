<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'laundry_order_id',
        'service_type',
        'partner_name',
        'status',
        'fee',
        'pickup_scheduled_at',
        'delivery_scheduled_at',
        'picked_up_at',
        'delivered_at',
        'pickup_address',
        'delivery_address',
        'courier_notes',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'pickup_scheduled_at' => 'datetime',
            'delivery_scheduled_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class, 'laundry_order_id');
    }
}
