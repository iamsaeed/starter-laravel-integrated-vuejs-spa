<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openai' => [
        'api_key' => env('VITE_OPENAI_API_KEY'),
        'model' => env('VITE_OPENAI_MODEL', 'gpt-4o-mini'),
        'embedding_model' => env('VITE_OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
    ],

    'qdrant' => [
        'host' => env('VITE_QDRANT_HOST', 'http://localhost'),
        'port' => env('VITE_QDRANT_PORT', 6333),
        'api_key' => env('VITE_QDRANT_API_KEY'),
    ],

    'serp' => [
        'api_key' => env('VITE_SERP_API_KEY'),
        'engine' => env('VITE_SERP_ENGINE', 'google'),
    ],

];
