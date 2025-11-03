<?php

namespace Database\Seeders;

use App\Models\SettingList;
use Illuminate\Database\Seeder;

class SettingListsSeeder extends Seeder
{
    public function run(): void
    {
        $settingLists = [
            // Date Formats
            [
                'key' => 'date_formats',
                'label' => 'YYYY-MM-DD (2025-10-01)',
                'value' => 'Y-m-d',
                'metadata' => json_encode(['example' => '2025-10-01', 'format' => 'ISO 8601']),
                'is_active' => true,
                'order' => 1,
            ],
            [
                'key' => 'date_formats',
                'label' => 'MM/DD/YYYY (10/01/2025)',
                'value' => 'm/d/Y',
                'metadata' => json_encode(['example' => '10/01/2025', 'format' => 'US Format']),
                'is_active' => true,
                'order' => 2,
            ],
            [
                'key' => 'date_formats',
                'label' => 'DD/MM/YYYY (01/10/2025)',
                'value' => 'd/m/Y',
                'metadata' => json_encode(['example' => '01/10/2025', 'format' => 'European Format']),
                'is_active' => true,
                'order' => 3,
            ],
            [
                'key' => 'date_formats',
                'label' => 'Month Day, Year (October 1, 2025)',
                'value' => 'F j, Y',
                'metadata' => json_encode(['example' => 'October 1, 2025', 'format' => 'Long Format']),
                'is_active' => true,
                'order' => 4,
            ],

            // Time Formats
            [
                'key' => 'time_formats',
                'label' => '24-hour (14:30:00)',
                'value' => 'H:i:s',
                'metadata' => json_encode(['example' => '14:30:00', 'format' => '24-hour']),
                'is_active' => true,
                'order' => 1,
            ],
            [
                'key' => 'time_formats',
                'label' => '12-hour (02:30 PM)',
                'value' => 'h:i A',
                'metadata' => json_encode(['example' => '02:30 PM', 'format' => '12-hour']),
                'is_active' => true,
                'order' => 2,
            ],
            [
                'key' => 'time_formats',
                'label' => '12-hour with seconds (02:30:00 PM)',
                'value' => 'h:i:s A',
                'metadata' => json_encode(['example' => '02:30:00 PM', 'format' => '12-hour with seconds']),
                'is_active' => true,
                'order' => 3,
            ],

            // Languages
            [
                'key' => 'languages',
                'label' => 'English',
                'value' => 'en',
                'metadata' => json_encode(['native_name' => 'English', 'flag' => '🇬🇧']),
                'is_active' => true,
                'order' => 1,
            ],
            [
                'key' => 'languages',
                'label' => 'Spanish',
                'value' => 'es',
                'metadata' => json_encode(['native_name' => 'Español', 'flag' => '🇪🇸']),
                'is_active' => true,
                'order' => 2,
            ],
            [
                'key' => 'languages',
                'label' => 'French',
                'value' => 'fr',
                'metadata' => json_encode(['native_name' => 'Français', 'flag' => '🇫🇷']),
                'is_active' => true,
                'order' => 3,
            ],
            [
                'key' => 'languages',
                'label' => 'German',
                'value' => 'de',
                'metadata' => json_encode(['native_name' => 'Deutsch', 'flag' => '🇩🇪']),
                'is_active' => true,
                'order' => 4,
            ],

            // Application Themes
            [
                'key' => 'themes',
                'label' => 'Default',
                'value' => 'default',
                'metadata' => json_encode([
                    'description' => 'Purple & Blue gradient theme',
                    'primary' => '#8b5cf6',
                    'secondary' => '#3b82f6',
                    'preview_gradient' => 'linear-gradient(135deg, #8b5cf6 0%, #3b82f6 100%)',
                ]),
                'is_active' => true,
                'order' => 1,
            ],
            [
                'key' => 'themes',
                'label' => 'Ocean',
                'value' => 'ocean',
                'metadata' => json_encode([
                    'description' => 'Blue & Teal gradient theme',
                    'primary' => '#3b82f6',
                    'secondary' => '#14b8a6',
                    'preview_gradient' => 'linear-gradient(135deg, #3b82f6 0%, #14b8a6 100%)',
                ]),
                'is_active' => true,
                'order' => 2,
            ],
            [
                'key' => 'themes',
                'label' => 'Sunset',
                'value' => 'sunset',
                'metadata' => json_encode([
                    'description' => 'Orange & Rose gradient theme',
                    'primary' => '#f97316',
                    'secondary' => '#f43f5e',
                    'preview_gradient' => 'linear-gradient(135deg, #f97316 0%, #f43f5e 100%)',
                ]),
                'is_active' => true,
                'order' => 3,
            ],
            [
                'key' => 'themes',
                'label' => 'Forest',
                'value' => 'forest',
                'metadata' => json_encode([
                    'description' => 'Green & Emerald gradient theme',
                    'primary' => '#22c55e',
                    'secondary' => '#10b981',
                    'preview_gradient' => 'linear-gradient(135deg, #22c55e 0%, #10b981 100%)',
                ]),
                'is_active' => true,
                'order' => 4,
            ],
            [
                'key' => 'themes',
                'label' => 'Midnight',
                'value' => 'midnight',
                'metadata' => json_encode([
                    'description' => 'Deep Indigo & Purple gradient theme',
                    'primary' => '#6366f1',
                    'secondary' => '#a855f7',
                    'preview_gradient' => 'linear-gradient(135deg, #6366f1 0%, #a855f7 100%)',
                ]),
                'is_active' => true,
                'order' => 5,
            ],
            [
                'key' => 'themes',
                'label' => 'Crimson',
                'value' => 'crimson',
                'metadata' => json_encode([
                    'description' => 'Red & Pink gradient theme',
                    'primary' => '#ef4444',
                    'secondary' => '#ec4899',
                    'preview_gradient' => 'linear-gradient(135deg, #ef4444 0%, #ec4899 100%)',
                ]),
                'is_active' => true,
                'order' => 6,
            ],
            [
                'key' => 'themes',
                'label' => 'Amber',
                'value' => 'amber',
                'metadata' => json_encode([
                    'description' => 'Yellow & Orange gradient theme',
                    'primary' => '#f59e0b',
                    'secondary' => '#f97316',
                    'preview_gradient' => 'linear-gradient(135deg, #f59e0b 0%, #f97316 100%)',
                ]),
                'is_active' => true,
                'order' => 7,
            ],
            [
                'key' => 'themes',
                'label' => 'Slate',
                'value' => 'slate',
                'metadata' => json_encode([
                    'description' => 'Cool Gray & Blue Gray gradient theme',
                    'primary' => '#64748b',
                    'secondary' => '#0ea5e9',
                    'preview_gradient' => 'linear-gradient(135deg, #64748b 0%, #0ea5e9 100%)',
                ]),
                'is_active' => true,
                'order' => 8,
            ],
            [
                'key' => 'themes',
                'label' => 'Lavender',
                'value' => 'lavender',
                'metadata' => json_encode([
                    'description' => 'Soft Purple & Mauve gradient theme',
                    'primary' => '#a855f7',
                    'secondary' => '#d946ef',
                    'preview_gradient' => 'linear-gradient(135deg, #a855f7 0%, #d946ef 100%)',
                ]),
                'is_active' => true,
                'order' => 9,
            ],

            // Admin Layouts
            [
                'key' => 'admin_layouts',
                'label' => 'Classic Sidebar',
                'value' => 'classic',
                'metadata' => json_encode([
                    'description' => 'Traditional left sidebar with top navbar',
                    'icon' => 'layout-sidebar',
                    'features' => ['Collapsible sidebar', 'Top navbar', 'Traditional layout'],
                    'navigation_type' => 'vertical',
                    'content_width' => 'full',
                ]),
                'is_active' => true,
                'order' => 1,
            ],
            [
                'key' => 'admin_layouts',
                'label' => 'Horizontal Navigation',
                'value' => 'horizontal',
                'metadata' => json_encode([
                    'description' => 'Top horizontal menu with no sidebar',
                    'icon' => 'layout-navbar',
                    'features' => ['Full-width content', 'Horizontal menu', 'Dropdown navigation'],
                    'navigation_type' => 'horizontal',
                    'content_width' => 'full',
                ]),
                'is_active' => true,
                'order' => 2,
            ],
            [
                'key' => 'admin_layouts',
                'label' => 'Compact Sidebar',
                'value' => 'compact',
                'metadata' => json_encode([
                    'description' => 'Always-collapsed icon-only sidebar',
                    'icon' => 'layout-sidebar-right',
                    'features' => ['Icon-only navigation', 'Tooltip labels', 'Maximum content space'],
                    'navigation_type' => 'vertical-icons',
                    'content_width' => 'full',
                ]),
                'is_active' => true,
                'order' => 3,
            ],
            [
                'key' => 'admin_layouts',
                'label' => 'Mini Sidebar',
                'value' => 'mini',
                'metadata' => json_encode([
                    'description' => 'Expandable sidebar that grows on hover',
                    'icon' => 'layout-sidebar-left',
                    'features' => ['Hover to expand', 'Space efficient', 'Quick access'],
                    'navigation_type' => 'vertical-mini',
                    'content_width' => 'full',
                ]),
                'is_active' => true,
                'order' => 4,
            ],
        ];

        foreach ($settingLists as $settingList) {
            SettingList::updateOrCreate(
                ['key' => $settingList['key'], 'value' => $settingList['value']],
                $settingList
            );
        }
    }
}
