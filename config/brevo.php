<?php

/*
|--------------------------------------------------------------------------
| Brevo (formerly Sendinblue) — Email, SMS & WhatsApp
|--------------------------------------------------------------------------
|
| Official PHP SDK: https://developers.brevo.com/guides/php
|
| API key (recommended for email):
|   Brevo dashboard → SMTP & API → API Keys → Create API key
|   Paste into .env as BREVO_API_KEY=xkeysib-...
|
| SMTP (alternative for email):
|   Brevo dashboard → SMTP & API → SMTP keys
|   BREVO_SMTP_USERNAME = your Brevo login email
|   BREVO_SMTP_PASSWORD = SMTP key (NOT the API key)
|
| Sender email must be verified under Senders & IP in Brevo.
|
*/

return [

    'enabled' => env('BREVO_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Email transport: api (recommended) or smtp
    |--------------------------------------------------------------------------
    */
    'transport' => env('BREVO_TRANSPORT', 'api'),

    /*
    |--------------------------------------------------------------------------
    | API key — used for transactional email (api transport), SMS & WhatsApp
    |--------------------------------------------------------------------------
    */
    'api_key' => env('BREVO_API_KEY'),

    'timeout' => (int) env('BREVO_TIMEOUT', 30),

    'max_retries' => (int) env('BREVO_MAX_RETRIES', 3),

    'sender' => [
        'email' => env('BREVO_SENDER_EMAIL', env('MAIL_FROM_ADDRESS', env('STORE_SUPPORT_EMAIL', 'bikeworld707@gmail.com'))),
        'name' => env('BREVO_SENDER_NAME', env('MAIL_FROM_NAME', env('APP_NAME', 'BikeWorld'))),
    ],

    'smtp' => [
        'host' => env('BREVO_SMTP_HOST', 'smtp-relay.brevo.com'),
        'port' => env('BREVO_SMTP_PORT', 587),
        'username' => env('BREVO_SMTP_USERNAME'),
        'password' => env('BREVO_SMTP_PASSWORD'),
        'encryption' => env('BREVO_SMTP_ENCRYPTION', 'tls'),
    ],

    'sms_enabled' => env('BREVO_SMS_ENABLED', false),

    'whatsapp_enabled' => env('BREVO_WHATSAPP_ENABLED', false),

    'sms_sender' => env('BREVO_SMS_SENDER', env('APP_NAME', 'BikeWorld')),

    'queue_notifications' => env('MAIL_QUEUE_NOTIFICATIONS', true),

];
