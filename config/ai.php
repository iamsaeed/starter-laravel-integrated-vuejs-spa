<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI & MCP Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for AI agents and MCP (Model Context
    | Protocol) servers used throughout the application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | MCP Servers
    |--------------------------------------------------------------------------
    |
    | Here you can register all MCP servers that your application uses.
    | Each server should have a unique key and the class that implements it.
    |
    */

    'mcp_servers' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Agents
    |--------------------------------------------------------------------------
    |
    | All registered AI agents in the multi-agent system.
    |
    */

    'agents' => [
        'supervisor' => \App\Neuron\SupervisorAgent::class,
        'conversation' => \App\Neuron\ConversationAgent::class,
        'research' => \App\Neuron\ResearchAgent::class,
        'analysis' => \App\Neuron\AnalysisAgent::class,
        'email_drafting' => \App\Neuron\EmailDraftingAgent::class,
        'blog_writing' => \App\Neuron\BlogWritingAgent::class,
        'search_engine' => \App\Neuron\SearchEngineAgent::class,
        'email_design' => \App\Neuron\EmailDesignAgent::class,
        'code' => \App\Neuron\CodeAgent::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Agent
    |--------------------------------------------------------------------------
    |
    | The default AI agent class to use for general conversation.
    |
    */

    'default_agent' => \App\Neuron\ConversationAgent::class,

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    |
    | The default AI provider to use across all agents. This can be overridden
    | per-agent by setting the 'provider' property or method.
    | Supported: 'openai', 'anthropic', 'deepseek', 'gemini'
    |
    */

    'default_provider' => env('AI_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | AI Provider Configurations
    |--------------------------------------------------------------------------
    |
    | Configuration for each AI provider. You can switch providers by changing
    | the 'default_provider' value above, or override per-agent.
    |
    */

    'providers' => [
        'openai' => [
            'api_key' => env('VITE_OPENAI_API_KEY'),
            'model' => env('VITE_OPENAI_MODEL', 'gpt-4o-mini'),
            'embedding_model' => env('VITE_OPENAI_EMBEDDING_MODEL', 'text-embedding-3-small'),
        ],

        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
            'version' => env('ANTHROPIC_VERSION', '2023-06-01'),
            'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 8192),
        ],

        'deepseek' => [
            'api_key' => env('VITE_DEEPSEEK_API_KEY'),
            'model' => env('VITE_DEEPSEEK_MODEL', 'deepseek-chat'),
        ],

        'gemini' => [
            'api_key' => env('VITE_GEMINI_API_KEY'),
            'model' => env('VITE_GEMINI_MODEL', 'gemini-2.0-flash-exp'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent-Specific Provider Overrides
    |--------------------------------------------------------------------------
    |
    | Override the AI provider for specific agents. Leave empty to use the
    | default provider. The key is the agent name from the 'agents' array.
    |
    */

    'agent_providers' => [
        // Example: Force supervisor to use Anthropic
        // 'supervisor' => 'anthropic',

        // Example: Force code agent to use DeepSeek
        // 'code' => 'deepseek',
    ],

    /*
    |--------------------------------------------------------------------------
    | Qdrant Configuration
    |--------------------------------------------------------------------------
    |
    | Qdrant vector database configuration for RAG systems.
    |
    */

    'qdrant' => [
        'host' => env('VITE_QDRANT_HOST', 'http://localhost'),
        'port' => env('VITE_QDRANT_PORT', 6333),
        'api_key' => env('VITE_QDRANT_API_KEY'),
        'collection' => env('VITE_QDRANT_COLLECTION', 'default'),
    ],

];
