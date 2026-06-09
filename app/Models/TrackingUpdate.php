<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'laundry_order_id',
        'status',
        'title',
        'description',
        'is_visible_to_customer',
        'sent_to_customer',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_visible_to_customer' => 'boolean',
            'sent_to_customer' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class, 'laundry_order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
