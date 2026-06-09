<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laundry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'phone',
        'address',
        'description',
        'subscription_plan',
        'commission_rate',
        'service_fee_rate',
        'is_active',
        'premium_protection_enabled',
        'supports_white_label',
        'network_name',
        'brand_name',
        'brand_primary_color',
        'brand_secondary_color',
        'custom_domain',
        'merchant_score',
        'score_last_calculated_at',
        'city',
        'rating',
        'review_count',
        'operating_hours',
        'photo_url',
        'pickup_available',
        'delivery_available',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'service_fee_rate' => 'decimal:2',
            'is_active' => 'boolean',
            'premium_protection_enabled' => 'boolean',
            'supports_white_label' => 'boolean',
            'merchant_score'          => 'decimal:2',
            'rating'                  => 'decimal:2',
            'score_last_calculated_at'=> 'datetime',
            'pickup_available'        => 'boolean',
            'delivery_available'      => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(LaundryOrder::class);
    }
}
