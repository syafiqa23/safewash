@props(['status' => '', 'size' => 'md'])

@php
    $colors = [
        // Order statuses
        'received'         => ['bg' => '#dbeafe', 'fg' => '#1e40af'],
        'washing'          => ['bg' => '#cffafe', 'fg' => '#155e75'],
        'drying'           => ['bg' => '#e0f2fe', 'fg' => '#0369a1'],
        'ironing'          => ['bg' => '#ede9fe', 'fg' => '#5b21b6'],
        'ready_for_pickup' => ['bg' => '#fef3c7', 'fg' => '#92400e'],
        'completed'        => ['bg' => '#d1fae5', 'fg' => '#065f46'],
        'claimed'          => ['bg' => '#ffedd5', 'fg' => '#9a3412'],
        // Payment statuses
        'paid'             => ['bg' => '#d1fae5', 'fg' => '#065f46'],
        'pending'          => ['bg' => '#fef3c7', 'fg' => '#92400e'],
        // Delivery statuses
        'scheduled'        => ['bg' => '#e0f2fe', 'fg' => '#0369a1'],
        'picked_up'        => ['bg' => '#e0e7ff', 'fg' => '#3730a3'],
        'in_transit'       => ['bg' => '#dbeafe', 'fg' => '#1e40af'],
        'out_for_delivery' => ['bg' => '#ede9fe', 'fg' => '#5b21b6'],
        'delivered'        => ['bg' => '#d1fae5', 'fg' => '#065f46'],
        'cancelled'        => ['bg' => '#fee2e2', 'fg' => '#991b1b'],
        // Payment edge cases
        'expired'          => ['bg' => '#f1f5f9', 'fg' => '#475569'],
        'failed'           => ['bg' => '#fee2e2', 'fg' => '#991b1b'],
        'integration_error'=> ['bg' => '#fef3c7', 'fg' => '#92400e'],
        // Claim statuses
        'open'             => ['bg' => '#fef3c7', 'fg' => '#92400e'],
        'investigating'    => ['bg' => '#dbeafe', 'fg' => '#1e40af'],
        'submitted'        => ['bg' => '#fef3c7', 'fg' => '#92400e'],
        'resolved'         => ['bg' => '#d1fae5', 'fg' => '#065f46'],
        'rejected'         => ['bg' => '#fee2e2', 'fg' => '#991b1b'],
        // Merchant statuses
        'active'           => ['bg' => '#d1fae5', 'fg' => '#065f46'],
        'inactive'         => ['bg' => '#f1f5f9', 'fg' => '#475569'],
        // Notification statuses
        'sent'             => ['bg' => '#d1fae5', 'fg' => '#065f46'],
        'simulated'        => ['bg' => '#f1f5f9', 'fg' => '#475569'],
        'failed'           => ['bg' => '#fee2e2', 'fg' => '#991b1b'],
    ];

    $labels = [
        'received'         => 'Received',
        'washing'          => 'Washing',
        'drying'           => 'Drying',
        'ironing'          => 'Ironing',
        'ready_for_pickup' => 'Ready for Pickup',
        'completed'        => 'Completed',
        'claimed'          => 'Claimed',
        'paid'             => 'Paid',
        'pending'          => 'Pending',
        'scheduled'        => 'Scheduled',
        'picked_up'        => 'Picked Up',
        'in_transit'       => 'In Transit',
        'out_for_delivery' => 'Out for Delivery',
        'delivered'        => 'Delivered',
        'cancelled'        => 'Cancelled',
        'open'             => 'Open',
        'investigating'    => 'Investigating',
        'submitted'        => 'Submitted',
        'resolved'         => 'Resolved',
        'rejected'         => 'Rejected',
        'active'           => 'Active',
        'inactive'         => 'Inactive',
        'sent'              => 'Sent',
        'simulated'         => 'Simulated',
        'failed'            => 'Failed',
        'expired'           => 'Expired',
        'integration_error' => 'Gateway Error',
    ];

    $icons = [
        'received'         => 'clock',
        'washing'          => 'droplets',
        'drying'           => 'wind',
        'ironing'          => 'zap',
        'ready_for_pickup' => 'package-check',
        'completed'        => 'check-circle-2',
        'claimed'          => 'alert-triangle',
        'paid'             => 'credit-card',
        'pending'          => 'clock',
        'scheduled'        => 'calendar',
        'picked_up'        => 'map-pin',
        'in_transit'       => 'truck',
        'out_for_delivery' => 'navigation',
        'delivered'        => 'check-circle-2',
        'cancelled'        => 'x-circle',
        'open'             => 'alert-circle',
        'investigating'    => 'search',
        'submitted'        => 'file-text',
        'resolved'         => 'check-circle',
        'rejected'         => 'x-circle',
        'active'           => 'check-circle',
        'inactive'         => 'minus-circle',
        'sent'              => 'send',
        'simulated'         => 'cpu',
        'failed'            => 'x-circle',
        'expired'           => 'clock',
        'integration_error' => 'alert-triangle',
    ];

    $c     = $colors[$status] ?? ['bg' => '#f1f5f9', 'fg' => '#475569'];
    $label = $labels[$status]  ?? ucwords(str_replace('_', ' ', $status));
    $icon  = $icons[$status]   ?? 'circle';
    $px    = $size === 'sm' ? '11px' : '12.5px';
    $iw    = $size === 'sm' ? '10px' : '11px';
    $pad   = $size === 'sm' ? '2px 8px' : '3px 10px';
@endphp

<span
    {{ $attributes }}
    style="display:inline-flex; align-items:center; gap:4px; padding:{{ $pad }}; border-radius:999px; background:{{ $c['bg'] }}; color:{{ $c['fg'] }}; font-size:{{ $px }}; font-weight:700; white-space:nowrap; line-height:1.6;"
>
    <i data-lucide="{{ $icon }}" style="width:{{ $iw }};height:{{ $iw }};flex-shrink:0;"></i>
    {{ $label }}
</span>
