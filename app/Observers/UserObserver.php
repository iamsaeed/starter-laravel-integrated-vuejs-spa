<?php

namespace App\Observers;

use App\Models\Setting;
use App\Models\SettingList;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        // Get global default theme
        $defaultTheme = config('settings.defaults.theme', 'default');
        $themeList = SettingList::where('key', 'themes')
            ->where('value', $defaultTheme)
            ->first();

        // Create default user settings
        $defaultSettings = [
            [
                'key' => 'user_theme',
                'value' => json_encode($defaultTheme),
                'type' => 'reference',
                'group' => 'appearance',
                'scope' => 'user',
                'label' => 'Theme',
                'description' => 'User interface color theme',
                'icon' => 'palette',
                'is_public' => true,
                'settable_type' => User::class,
                'settable_id' => $user->id,
                'referenceable_type' => $themeList ? SettingList::class : null,
                'referenceable_id' => $themeList?->id,
                'order' => 1,
            ],
            [
                'key' => 'dark_mode',
                'value' => json_encode(config('settings.defaults.dark_mode', false)),
                'type' => 'boolean',
                'group' => 'appearance',
                'scope' => 'user',
                'label' => 'Dark Mode',
                'description' => 'Enable dark mode for the interface',
                'icon' => 'moon',
                'is_public' => true,
                'settable_type' => User::class,
                'settable_id' => $user->id,
                'referenceable_type' => null,
                'referenceable_id' => null,
                'order' => 2,
            ],
            [
                'key' => 'items_per_page',
                'value' => json_encode(config('settings.defaults.items_per_page', 25)),
                'type' => 'integer',
                'group' => 'general',
                'scope' => 'user',
                'label' => 'Items Per Page',
                'description' => 'Number of items to display per page',
                'icon' => 'list',
                'is_public' => true,
                'settable_type' => User::class,
                'settable_id' => $user->id,
                'referenceable_type' => null,
                'referenceable_id' => null,
                'order' => 1,
            ],
        ];

        foreach ($defaultSettings as $settingData) {
            Setting::create($settingData);
        }
    }

    public function updated(User $user): void
    {
        // Only sync if relevant fields changed
        // User cache syncing removed (workspace functionality was removed)
    }

    public function deleting(User $user): void
    {
        // Clean up user settings when user is deleted
        $user->settings()->delete();
    }
}
