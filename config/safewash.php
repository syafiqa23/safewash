<?php

return [
    'payment' => [
        'provider' => env('SAFEWASH_PAYMENT_PROVIDER', 'simulator'),
        'mode' => env('SAFEWASH_PAYMENT_MODE', 'sandbox'),
        'gateway_name' => env('SAFEWASH_PAYMENT_GATEWAY', 'SafeWash Pay Simulator'),
        'supported_methods' => [
            'cash' => 'Cash',
            'qris' => 'QRIS',
            'ewallet' => 'E-Wallet',
            'virtual_account' => 'Virtual Account',
        ],
        'gateway_fee_rate' => 1.50,
    ],
    'whatsapp' => [
        'enabled' => env('SAFEWASH_WHATSAPP_ENABLED', false),
        'gateway_name' => env('SAFEWASH_WHATSAPP_GATEWAY', 'SafeWash WA Gateway'),
        'sender' => env('SAFEWASH_WHATSAPP_SENDER', 'SafeWash Bot'),
    ],
    'loyalty' => [
        'earn_rate_per_5000' => 1,
        'minimum_points_per_order' => 10,
        'tiers' => [
            'Ocean' => 0,
            'Sky' => 200,
            'Cloud' => 500,
            'Aurora' => 1000,
        ],
    ],
    'delivery' => [
        'default_partner' => env('SAFEWASH_DELIVERY_PARTNER', 'SafeWash Courier'),
        'supported_types' => [
            'none' => 'Outlet Only',
            'pickup' => 'Pickup',
            'delivery' => 'Delivery',
            'round_trip' => 'Pickup + Delivery',
        ],
    ],
];
