<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Neuron-AI Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Neuron-AI chat workflow system
    |
    */

    'workflows' => [
        'chat' => [
            'class' => \App\Neuron\Workflows\ChatWorkflow::class,
            'enabled_tools' => [
                'conversation' => true,
                'database' => true,
                'search' => true,
                'email' => false,  // Disabled for MVP
            ],
            'ai_provider' => env('AI_PROVIDER', 'openai'),
            'model' => env('AI_MODEL', 'gpt-4o-mini'),
            'max_tokens' => 2000,
            'temperature' => 0.7,

            // AI Intent Classification Configuration
            'use_ai_intent' => env('NEURON_USE_AI_INTENT', true),
            'intent_hybrid_mode' => env('NEURON_INTENT_HYBRID', false),
            'intent_fallback_keywords' => false,
            'intent_confidence_threshold' => 0.5,
            'intent_log_decisions' => env('NEURON_LOG_INTENT', false),
        ],
    ],

    'tools' => [
        'database' => [
            'allowed_tables' => ['documents', 'users'],
            'max_records' => 100,
            'allow_writes' => true,
        ],
        'search' => [
            'providers' => ['internal'],
            'max_results' => 10,
            'use_ai_answer' => env('NEURON_SEARCH_USE_AI_ANSWER', true), // Use SearchAndAnswerTool vs basic SearchEngineTool
        ],
        'search_answer' => [
            'max_sources' => env('NEURON_SEARCH_MAX_SOURCES', 3), // Number of URLs to fetch and analyze
        ],
        'web_fetcher' => [
            'timeout' => 30, // HTTP request timeout in seconds
            'max_content_length' => 10000, // Maximum characters to extract from a web page
        ],
    ],

    'ai' => [
        'provider' => env('AI_PROVIDER', 'openai'),
        'model' => env('AI_MODEL', 'gpt-4o-mini'),
    ],

    'security' => [
        'rate_limit' => 30,  // requests per minute
        'max_conversation_length' => 100,  // messages
        'require_authentication' => true,
        'log_all_interactions' => true,
    ],

    'providers' => [
        'openai' => [
            'api_key' => env('VITE_OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
        ],
        'gemini' => [
            'api_key' => env('VITE_GEMINI_API_KEY'),
            'model' => env('VITE_GEMINI_MODEL', 'gemini-1.5-pro'),
        ],
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
        ],
        'ollama' => [
            'base_url' => env('OLLAMA_BASE_URL', 'http://localhost:11434'),
        ],
    ],

    'monitoring' => [
        'enabled' => env('NEURON_MONITORING', false),
        'inspector_key' => env('INSPECTOR_INGESTION_KEY'),
    ],
];
