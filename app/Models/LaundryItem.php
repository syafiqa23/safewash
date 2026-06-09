<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaundryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'laundry_order_id',
        'item_name',
        'quantity',
        'condition_notes',
        'is_priority',
    ];

    protected function casts(): array
    {
        return [
            'is_priority' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class, 'laundry_order_id');
    }
}
