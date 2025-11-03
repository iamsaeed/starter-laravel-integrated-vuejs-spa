<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Theme
    |--------------------------------------------------------------------------
    |
    | This value is the default theme that will be used when a user has not
    | selected a theme preference. This should match one of the available
    | themes defined below.
    |
    */

    'default' => env('APP_THEME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Available Themes
    |--------------------------------------------------------------------------
    |
    | This array contains all available themes that users can select from.
    | Each theme should have a corresponding CSS file in resources/css/themes/
    |
    */

    'available' => [
        'default' => [
            'name' => 'Default',
            'description' => 'Purple & Blue gradient theme',
            'primary' => '#8b5cf6',
            'secondary' => '#3b82f6',
            'css_file' => 'themes/default.css',
        ],
        'ocean' => [
            'name' => 'Ocean',
            'description' => 'Blue & Teal gradient theme',
            'primary' => '#3b82f6',
            'secondary' => '#14b8a6',
            'css_file' => 'themes/ocean.css',
        ],
        'sunset' => [
            'name' => 'Sunset',
            'description' => 'Orange & Rose gradient theme',
            'primary' => '#f97316',
            'secondary' => '#f43f5e',
            'css_file' => 'themes/sunset.css',
        ],
        'forest' => [
            'name' => 'Forest',
            'description' => 'Green & Emerald gradient theme',
            'primary' => '#22c55e',
            'secondary' => '#10b981',
            'css_file' => 'themes/forest.css',
        ],
        'midnight' => [
            'name' => 'Midnight',
            'description' => 'Deep Indigo & Purple gradient theme',
            'primary' => '#6366f1',
            'secondary' => '#a855f7',
            'css_file' => 'themes/midnight.css',
        ],
        'crimson' => [
            'name' => 'Crimson',
            'description' => 'Red & Pink gradient theme',
            'primary' => '#ef4444',
            'secondary' => '#ec4899',
            'css_file' => 'themes/crimson.css',
        ],
        'amber' => [
            'name' => 'Amber',
            'description' => 'Yellow & Orange gradient theme',
            'primary' => '#f59e0b',
            'secondary' => '#f97316',
            'css_file' => 'themes/amber.css',
        ],
        'slate' => [
            'name' => 'Slate',
            'description' => 'Cool Gray & Blue Gray gradient theme',
            'primary' => '#64748b',
            'secondary' => '#0ea5e9',
            'css_file' => 'themes/slate.css',
        ],
        'lavender' => [
            'name' => 'Lavender',
            'description' => 'Soft Purple & Mauve gradient theme',
            'primary' => '#a855f7',
            'secondary' => '#d946ef',
            'css_file' => 'themes/lavender.css',
        ],
    ],
];
