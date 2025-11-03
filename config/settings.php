<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Settings for New Users
    |--------------------------------------------------------------------------
    | These values are used by UserObserver to create default settings
    | when a new user is registered.
    */
    'defaults' => [
        'theme' => env('DEFAULT_THEME', 'ocean'),
        'language' => env('DEFAULT_LANGUAGE', 'en'),
        'timezone' => env('DEFAULT_TIMEZONE', 'UTC'),
        'items_per_page' => 25,
        'dark_mode' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Setting Groups
    |--------------------------------------------------------------------------
    | Define all setting groups with their metadata for UI rendering
    */
    'groups' => [
        'general' => [
            'name' => 'General',
            'icon' => 'cog',
            'description' => 'General application settings',
            'order' => 1,
        ],
        'localization' => [
            'name' => 'Localization',
            'icon' => 'globe',
            'description' => 'Language, timezone, and regional settings',
            'order' => 2,
        ],
        'appearance' => [
            'name' => 'Appearance',
            'icon' => 'palette',
            'description' => 'Theme and display preferences',
            'order' => 3,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Setting Types
    |--------------------------------------------------------------------------
    */
    'types' => [
        'string', 'integer', 'boolean', 'array', 'json', 'reference',
    ],

    /*
    |--------------------------------------------------------------------------
    | Setting Scopes
    |--------------------------------------------------------------------------
    */
    'scopes' => [
        'global' => 'Application-wide settings',
        'user' => 'User-specific settings',
        'admin' => 'Administrator-only settings',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => env('SETTINGS_CACHE_ENABLED', true),
        'ttl' => env('SETTINGS_CACHE_TTL', 3600), // 1 hour
        'prefix' => 'settings:',
    ],
];
