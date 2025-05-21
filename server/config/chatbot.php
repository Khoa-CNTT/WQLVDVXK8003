<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chatbot API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your chatbot API settings.
    |
    */

'api_key' => env('DISTRIBUTEAI_API_KEY', '3712dc046a900b14f64adca011dc98db'),
    'timeout' => env('CHATBOT_API_TIMEOUT', 30),
    'cache_ttl' => env('CHATBOT_CACHE_TTL', 3600), // 1 hour
];
