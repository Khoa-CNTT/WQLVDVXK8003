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

// Thay thế key cứng bằng:
$api_key = $_ENV['ANTHROPIC_API_KEY'] ?? getenv('ANTHROPIC_API_KEY'),
    'base_url' => 'https://api.anthropic.com/v1',
    'timeout' => env('CHATBOT_API_TIMEOUT', 30),
    'cache_ttl' => env('CHATBOT_CACHE_TTL', 3600), // 1 hour
    'model' => 'claude-3-5-haiku-20241022',
];
