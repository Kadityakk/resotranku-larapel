<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Midtrans Credentials
    |--------------------------------------------------------------------------
    |
    | Grab these from the Midtrans dashboard under Settings > Access Keys.
    | The client key is safe to expose in the browser; the server key is not.
    |
    */

    'merchant_id' => env('MIDTRANS_MERCHANT_ID'),

    'client_key' => env('MIDTRANS_CLIENT_KEY'),

    'server_key' => env('MIDTRANS_SERVER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Keep this false while developing so transactions hit the sandbox.
    |
    */

    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    /*
    |--------------------------------------------------------------------------
    | Snap.js
    |--------------------------------------------------------------------------
    |
    | URL of the Snap popup script, which differs between sandbox and production.
    |
    */

    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    /*
    |--------------------------------------------------------------------------
    | Enabled Payment Channels
    |--------------------------------------------------------------------------
    |
    | Leave empty to let Midtrans show every channel active on the account.
    |
    */

    'enabled_payments' => ['gopay', 'other_qris', 'shopeepay', 'bank_transfer'],

];
