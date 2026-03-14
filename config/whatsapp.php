<?php

return [
    'enabled' => env('WHATSAPP_ENABLED', false),
    'driver'  => env('WHATSAPP_DRIVER', 'fonnte'),
    'bot_number' => env('FONNTE_BOT_NUMBER', ''),
    'fonnte'  => [
        'token'          => env('FONNTE_TOKEN', ''),
        'endpoint'       => 'https://api.fonnte.com/send',
        'webhook_secret' => env('FONNTE_WEBHOOK_SECRET', ''),
    ],
    'wablas'  => [
        'token'    => env('WABLAS_TOKEN', ''),
        'endpoint' => env('WABLAS_ENDPOINT', ''),
    ],
];
