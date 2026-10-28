<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tax Rate
    |--------------------------------------------------------------------------
    |
    | Applied to the cart subtotal at checkout and stored on the order so a
    | later rate change never rewrites historical totals.
    |
    */

    'tax_rate' => env('RESTAURANT_TAX_RATE', 0.10),

    /*
    |--------------------------------------------------------------------------
    | Dining Tables
    |--------------------------------------------------------------------------
    |
    | Highest table number a QR code may point at.
    |
    */

    'table_count' => env('RESTAURANT_TABLE_COUNT', 30),

];
