<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MerchantSettlement extends Model
{
    protected $fillable = [
        'laundry_id',
        'period_start',
        'period_end',
        'order_count',
        'gross_amount',
        'commission_amount',
        'service_fee',
        'net_amount',
        'status',
        'settled_at',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start'      => 'date',
            'period_end'        => 'date',
            'gross_amount'      => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'service_fee'       => 'decimal:2',
            'net_amount'        => 'decimal:2',
            'settled_at'        => 'datetime',
        ];
    }

    public function laundry(): BelongsTo
    {
        return $this->belongsTo(Laundry::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
