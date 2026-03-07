<?php

return [
    'enabled' => env('WHATSAPP_ENABLED', false),
    'driver'  => env('WHATSAPP_DRIVER', 'fonnte'),
    'fonnte'  => [
        'token'    => env('FONNTE_TOKEN', ''),
        'endpoint' => 'https://api.fonnte.com/send',
    ],
    'wablas'  => [
        'token'    => env('WABLAS_TOKEN', ''),
        'endpoint' => env('WABLAS_ENDPOINT', ''),
    ],
];
