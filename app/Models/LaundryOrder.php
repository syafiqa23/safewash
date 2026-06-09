<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class LaundryOrder extends Model
{
    use HasFactory;

    public const STATUSES = [
        'received',
        'pickup',
        'washing',
        'drying',
        'ironing',
        'quality_check',
        'ready_delivery',
        'completed',
        'claimed',
    ];

    public const STATUS_LABELS = [
        'received'      => 'Order Diterima',
        'pickup'        => 'Pickup Courier',
        'washing'       => 'Sedang Dicuci',
        'drying'        => 'Sedang Dikeringkan',
        'ironing'       => 'Sedang Disetrika',
        'quality_check' => 'Quality Check',
        'ready_delivery'=> 'Siap Diantar',
        'completed'     => 'Selesai',
        'claimed'       => 'Diklaim',
    ];

    protected $fillable = [
        'laundry_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'service_type',
        'weight_kg',
        'total_price',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_paid_at',
        'tracking_code',
        'qr_token',
        'pickup_delivery_opt_in',
        'pickup_delivery_fee',
        'pickup_address',
        'delivery_address',
        'loyalty_points_earned',
        'is_premium_protected',
        'promised_at',
        'picked_up_at',
        'pickup_at',
        'delivered_at',
        'claim_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'total_price' => 'decimal:2',
            'pickup_delivery_fee' => 'decimal:2',
            'is_premium_protected' => 'boolean',
            'pickup_delivery_opt_in' => 'boolean',
            'payment_paid_at' => 'datetime',
            'promised_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'pickup_at'    => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (LaundryOrder $order): void {
            if (blank($order->tracking_code)) {
                $order->tracking_code = 'SW-'.Str::upper(Str::random(8));
            }

            if (blank($order->qr_token)) {
                $order->qr_token = (string) Str::uuid();
            }

            if (blank($order->claim_status)) {
                $order->claim_status = 'none';
            }
        });
    }

    public function laundry(): BelongsTo
    {
        return $this->belongsTo(Laundry::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LaundryItem::class);
    }

    public function trackingUpdates(): HasMany
    {
        return $this->hasMany(TrackingUpdate::class)->latest();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(NotificationLog::class)->latest();
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class)->latest();
    }

    public function paymentTransaction(): HasOne
    {
        return $this->hasOne(PaymentTransaction::class)->latestOfMany();
    }

    public function deliveryRequest(): HasOne
    {
        return $this->hasOne(DeliveryRequest::class)->latestOfMany();
    }
}
