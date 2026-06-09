<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Claim extends Model
{
    use HasFactory;

    public const TYPES = ['hilang', 'rusak', 'tertukar'];

    public const TYPE_LABELS = [
        'hilang'   => 'Barang Hilang',
        'rusak'    => 'Barang Rusak',
        'tertukar' => 'Barang Tertukar',
    ];

    public const STATUSES = [
        'submitted'    => 'Diajukan',
        'investigating'=> 'Investigasi',
        'approved'     => 'Disetujui',
        'rejected'     => 'Ditolak',
        'compensated'  => 'Dikompensasi',
        'closed'       => 'Ditutup',
    ];

    protected $fillable = [
        'laundry_order_id',
        'customer_id',
        'claimant_name',
        'claimant_contact',
        'claim_type',
        'item_name',
        'description',
        'photo_path',
        'loss_amount',
        'status',
        'compensation_amount',
        'resolution_notes',
        'submitted_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'compensation_amount' => 'decimal:2',
            'loss_amount'         => 'decimal:2',
            'submitted_at'        => 'datetime',
            'resolved_at'         => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(LaundryOrder::class, 'laundry_order_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
