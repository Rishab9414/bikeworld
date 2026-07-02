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
    'pickup_phone' => env('DELHIVERY_PICKUP_PHONE', '9999999999'),

    'default_weight_kg' => 0.5,
];
