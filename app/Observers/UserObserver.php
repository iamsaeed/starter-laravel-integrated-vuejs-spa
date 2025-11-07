<?php

namespace App\Observers;

use App\Models\SettingList;
use App\Models\User;
use App\Services\SettingsService;

class UserObserver
{
    public function created(User $user): void
    {
        // Get SettingsService for proper value encoding
        $settingsService = app(SettingsService::class);

        // Get global default theme
        $defaultTheme = config('settings.defaults.theme', 'default');
        $themeList = SettingList::where('key', 'themes')
            ->where('value', $defaultTheme)
            ->first();

        // Get global default admin layout
        $defaultLayout = config('settings.defaults.admin_layout', 'classic');
        $layoutList = SettingList::where('key', 'admin_layouts')
            ->where('value', $defaultLayout)
            ->first();

        // Get default dark mode
        $defaultDarkMode = config('settings.defaults.dark_mode', false);

        // Get default items per page
        $defaultItemsPerPage = config('settings.defaults.items_per_page', 25);

        // Create default user settings with proper metadata
        $defaultSettings = [
            [
                'key' => 'user_theme',
                'value' => $defaultTheme,
                'metadata' => [
                    'type' => 'reference',
                    'group' => 'appearance',
                    'label' => 'Theme',
                    'description' => 'User interface color theme',
                    'icon' => 'palette',
                    'is_public' => true,
                    'referenceable_type' => $themeList ? SettingList::class : null,
                    'referenceable_id' => $themeList?->id,
                    'order' => 1,
                ],
            ],
            [
                'key' => 'user_admin_layout',
                'value' => $defaultLayout,
                'metadata' => [
                    'type' => 'reference',
                    'group' => 'appearance',
                    'label' => 'Admin Layout',
                    'description' => 'Admin panel layout preference',
                    'icon' => 'layout',
                    'is_public' => true,
                    'referenceable_type' => $layoutList ? SettingList::class : null,
                    'referenceable_id' => $layoutList?->id,
                    'order' => 2,
                ],
            ],
            [
                'key' => 'dark_mode',
                'value' => $defaultDarkMode,
                'metadata' => [
                    'type' => 'boolean',
                    'group' => 'appearance',
                    'label' => 'Dark Mode',
                    'description' => 'Enable dark mode for the interface',
                    'icon' => 'moon',
                    'is_public' => true,
                    'order' => 3,
                ],
            ],
            [
                'key' => 'items_per_page',
                'value' => $defaultItemsPerPage,
                'metadata' => [
                    'type' => 'integer',
                    'group' => 'general',
                    'label' => 'Items Per Page',
                    'description' => 'Number of items to display per page',
                    'icon' => 'list',
                    'is_public' => true,
                    'order' => 1,
                ],
            ],
        ];

        // Create settings using SettingsService for proper encoding
        foreach ($defaultSettings as $settingData) {
            // Create the setting with proper value encoding
            $setting = $settingsService->set(
                $settingData['key'],
                $settingData['value'],
                'user',
                $user->id
            );

            // Update additional metadata fields
            $setting->update([
                'type' => $settingData['metadata']['type'],
                'group' => $settingData['metadata']['group'],
                'label' => $settingData['metadata']['label'],
                'description' => $settingData['metadata']['description'],
                'icon' => $settingData['metadata']['icon'],
                'is_public' => $settingData['metadata']['is_public'],
                'referenceable_type' => $settingData['metadata']['referenceable_type'] ?? null,
                'referenceable_id' => $settingData['metadata']['referenceable_id'] ?? null,
                'order' => $settingData['metadata']['order'],
            ]);
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
