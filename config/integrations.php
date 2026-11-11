<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External integrations
    |--------------------------------------------------------------------------
    |
    | Integrations are optional. The core commercial workflow must continue
    | to operate manually when a provider is not configured or unavailable.
    |
    */

    'payments' => [
        'default' => env('PAYMENT_GATEWAY'),
        'webhook_tolerance_seconds' => (int) env('PAYMENT_WEBHOOK_TOLERANCE', 300),
        'drivers' => [
            // Provider credentials belong in environment variables, never here.
        ],
    ],

    'suppliers' => [
        'default' => env('SUPPLIER_INTEGRATION'),
        'drivers' => [],
    ],

    'shipping' => [
        'default' => env('SHIPPING_INTEGRATION'),
        'drivers' => [],
    ],

    'notifications' => [
        'default' => env('NOTIFICATION_PROVIDER'),
        'drivers' => [],
    ],

];
