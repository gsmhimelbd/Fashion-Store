<?php

return [
    'stripe' => ['key' => env('STRIPE_KEY'), 'secret' => env('STRIPE_SECRET'), 'webhook_secret' => env('STRIPE_WEBHOOK_SECRET')],
    'paypal' => ['mode' => env('PAYPAL_MODE', 'sandbox'), 'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID'), 'client_secret' => env('PAYPAL_SANDBOX_CLIENT_SECRET')],
    'google_analytics' => ['measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID')],
    'whatsapp' => ['token' => env('WHATSAPP_TOKEN'), 'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID')],
];
