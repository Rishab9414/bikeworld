<?php

return [
    'key_id' => env('RAZORPAY_KEY_ID'),
    'key_secret' => env('RAZORPAY_KEY_SECRET'),
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
    'mock' => env('RAZORPAY_MOCK', false),
    'currency' => env('RAZORPAY_CURRENCY', 'INR'),
    'company_name' => env('RAZORPAY_COMPANY_NAME', env('APP_NAME', 'Bike World')),
];
