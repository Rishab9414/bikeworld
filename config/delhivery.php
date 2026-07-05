<?php

return [
    'api_token' => env('DELHIVERY_API_TOKEN', ''),
    'base_url' => env('DELHIVERY_BASE_URL', 'https://track.delhivery.com'),
    'mock' => env('DELHIVERY_MOCK', true),

    'pickup_location' => env('DELHIVERY_PICKUP_LOCATION', 'BikeWorld Warehouse'),
    'pickup_address' => env('DELHIVERY_PICKUP_ADDRESS', 'Warehouse Address'),
    'pickup_city' => env('DELHIVERY_PICKUP_CITY', 'Mumbai'),
    'pickup_state' => env('DELHIVERY_PICKUP_STATE', 'Maharashtra'),
    'pickup_pin' => env('DELHIVERY_PICKUP_PIN', '400001'),
    'pickup_phone' => env('DELHIVERY_PICKUP_PHONE', env('STORE_SUPPORT_PHONE', '9743663260')),

    'default_weight_kg' => 0.5,
    'default_shipping_charge' => env('DELHIVERY_DEFAULT_SHIPPING', 99),
    'shipping_mode' => env('DELHIVERY_SHIPPING_MODE', 'E'),
    'client_name' => env('DELHIVERY_CLIENT_NAME', ''),
];
