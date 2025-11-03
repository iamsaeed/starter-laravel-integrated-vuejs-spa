<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Setting;
use App\Models\SettingList;
use App\Models\Timezone;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // Get default references
        $defaultTheme = SettingList::where('key', 'themes')->where('value', 'ocean')->first();
        $defaultLayout = SettingList::where('key', 'admin_layouts')->where('value', 'classic')->first();
        $defaultCountry = Country::where('code', 'US')->first();
        $defaultTimezone = Timezone::where('name', 'America/New_York')->first();
        $defaultDateFormat = SettingList::where('key', 'date_formats')->where('value', 'Y-m-d')->first();
        $defaultTimeFormat = SettingList::where('key', 'time_formats')->where('value', 'H:i:s')->first();
        $defaultLanguage = SettingList::where('key', 'languages')->where('value', 'en')->first();

        $globalSettings = [
            // General Group
            [
                'key' => 'site_name',
                'value' => json_encode('My Application'),
                'type' => 'string',
                'group' => 'general',
                'scope' => 'global',
                'label' => 'Site Name',
                'description' => 'The name of the application',
                'icon' => 'globe',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => json_encode(['required', 'string', 'max:255']),
                'settable_type' => null,
                'settable_id' => null,
                'referenceable_type' => null,
                'referenceable_id' => null,
                'order' => 1,
            ],
            [
                'key' => 'maintenance_mode',
                'value' => json_encode(false),
                'type' => 'boolean',
                'group' => 'general',
                'scope' => 'global',
                'label' => 'Maintenance Mode',
                'description' => 'Enable or disable maintenance mode',
                'icon' => 'wrench',
                'is_public' => false,
                'is_encrypted' => false,
                'validation_rules' => json_encode(['boolean']),
                'settable_type' => null,
                'settable_id' => null,
                'referenceable_type' => null,
                'referenceable_id' => null,
                'order' => 2,
            ],

            // Localization Group
            [
                'key' => 'default_timezone',
                'value' => json_encode('America/New_York'),
                'type' => 'reference',
                'group' => 'localization',
                'scope' => 'global',
                'label' => 'Default Timezone',
                'description' => 'Default timezone for new users',
                'icon' => 'clock',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => json_encode(['required']),
                'settable_type' => null,
                'settable_id' => null,
                'referenceable_type' => $defaultTimezone ? Timezone::class : null,
                'referenceable_id' => $defaultTimezone?->id,
                'order' => 1,
            ],
            [
                'key' => 'default_country',
                'value' => json_encode('US'),
                'type' => 'reference',
                'group' => 'localization',
                'scope' => 'global',
                'label' => 'Default Country',
                'description' => 'Default country for new users',
                'icon' => 'flag',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => json_encode(['required']),
                'settable_type' => null,
                'settable_id' => null,
                'referenceable_type' => $defaultCountry ? Country::class : null,
                'referenceable_id' => $defaultCountry?->id,
                'order' => 2,
            ],
            [
                'key' => 'default_date_format',
                'value' => json_encode('Y-m-d'),
                'type' => 'reference',
                'group' => 'localization',
                'scope' => 'global',
                'label' => 'Default Date Format',
                'description' => 'Default date format for the application',
                'icon' => 'calendar',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => json_encode(['required']),
                'settable_type' => null,
                'settable_id' => null,
                'referenceable_type' => $defaultDateFormat ? SettingList::class : null,
                'referenceable_id' => $defaultDateFormat?->id,
                'order' => 3,
            ],
            [
                'key' => 'default_time_format',
                'value' => json_encode('H:i:s'),
                'type' => 'reference',
                'group' => 'localization',
                'scope' => 'global',
                'label' => 'Default Time Format',
                'description' => 'Default time format for the application',
                'icon' => 'clock',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => json_encode(['required']),
                'settable_type' => null,
                'settable_id' => null,
                'referenceable_type' => $defaultTimeFormat ? SettingList::class : null,
                'referenceable_id' => $defaultTimeFormat?->id,
                'order' => 4,
            ],
            [
                'key' => 'default_language',
                'value' => json_encode('en'),
                'type' => 'reference',
                'group' => 'localization',
                'scope' => 'global',
                'label' => 'Default Language',
                'description' => 'Default language for the application',
                'icon' => 'language',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => json_encode(['required']),
                'settable_type' => null,
                'settable_id' => null,
                'referenceable_type' => $defaultLanguage ? SettingList::class : null,
                'referenceable_id' => $defaultLanguage?->id,
                'order' => 5,
            ],

            // Appearance Group
            [
                'key' => 'default_theme',
                'value' => json_encode('ocean'),
                'type' => 'reference',
                'group' => 'appearance',
                'scope' => 'global',
                'label' => 'Default Theme',
                'description' => 'Default theme for new users',
                'icon' => 'palette',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => json_encode(['required']),
                'settable_type' => null,
                'settable_id' => null,
                'referenceable_type' => $defaultTheme ? SettingList::class : null,
                'referenceable_id' => $defaultTheme?->id,
                'order' => 1,
            ],
            [
                'key' => 'default_admin_layout',
                'value' => json_encode('classic'),
                'type' => 'reference',
                'group' => 'appearance',
                'scope' => 'global',
                'label' => 'Default Admin Layout',
                'description' => 'Default admin panel layout for new users',
                'icon' => 'layout',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => json_encode(['required']),
                'settable_type' => null,
                'settable_id' => null,
                'referenceable_type' => $defaultLayout ? SettingList::class : null,
                'referenceable_id' => $defaultLayout?->id,
                'order' => 2,
            ],
            [
                'key' => 'items_per_page',
                'value' => json_encode(25),
                'type' => 'integer',
                'group' => 'appearance',
                'scope' => 'global',
                'label' => 'Items Per Page',
                'description' => 'Default number of items per page',
                'icon' => 'list',
                'is_public' => true,
                'is_encrypted' => false,
                'validation_rules' => json_encode(['required', 'integer', 'min:10', 'max:100']),
                'settable_type' => null,
                'settable_id' => null,
                'referenceable_type' => null,
                'referenceable_id' => null,
                'order' => 3,
            ],
        ];

        foreach ($globalSettings as $setting) {
            Setting::updateOrCreate(
                [
                    'key' => $setting['key'],
                    'scope' => $setting['scope'],
                    'settable_type' => $setting['settable_type'],
                    'settable_id' => $setting['settable_id'],
                ],
                $setting
            );
        }
    }
}
