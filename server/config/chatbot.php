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

    'api_key' => env('CHATBOT_API_KEY', '2e0b70a2689f75af08e6586f9b6a9f6d'),
    'base_url' => env('CHATBOT_API_URL', 'https://chatbot-api.phuongthanh.com/v1'),
    'timeout' => env('CHATBOT_API_TIMEOUT', 30),
    'cache_ttl' => env('CHATBOT_CACHE_TTL', 3600), // 1 hour
];
