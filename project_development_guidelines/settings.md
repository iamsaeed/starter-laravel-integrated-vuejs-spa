# Comprehensive Settings System Implementation Plan

## Overview
Build a flexible, scalable settings system with:
- Key-value pairs with type casting
- Polymorphic relationships to reference other tables (countries, timezones, etc.)
- Group-based organization
- Role/scope-based settings (global, user-specific, admin-specific)
- Icons and metadata support
- Optional relationship to settings_lists for predefined options

## Database Architecture

### 1. Core Tables

#### `settings` (Main settings table)
- `id` - Primary key
- `key` - Setting key (unique per scope, e.g., 'timezone', 'date_format')
- `value` - JSON field to store the actual value
- `type` - Enum: 'string', 'integer', 'boolean', 'array', 'json', 'reference'
- `group` - Group name (e.g., 'general', 'localization', 'appearance', 'notifications')
- `scope` - Enum: 'global', 'user', 'admin' (who can access this setting)
- `icon` - Icon name/class for UI display
- `label` - Human-readable label
- `description` - Help text
- `is_public` - Boolean: whether users can see/modify this
- `is_encrypted` - Boolean: for sensitive values
- `validation_rules` - JSON: Laravel validation rules
- `settable_type` - Nullable: polymorphic type (for user-specific settings)
- `settable_id` - Nullable: polymorphic id
- `referenceable_type` - Nullable: what table this references (Country, Timezone, etc.)
- `referenceable_id` - Nullable: the specific record id
- `order` - Integer: display order within group
- `timestamps`

#### `setting_lists` (Predefined options/choices)
- `id` - Primary key
- `key` - List identifier (e.g., 'date_formats', 'time_formats', 'languages')
- `label` - Display label
- `value` - The actual value to store
- `metadata` - JSON: extra data (icon, description, etc.)
- `is_active` - Boolean
- `order` - Display order
- `timestamps`

#### `countries` (Reference table - Enhanced)
- `id` - Primary key
- `code` - ISO 3166-1 alpha-2 code (2 letters, e.g., 'US', 'GB')
- `code_alpha3` - ISO 3166-1 alpha-3 code (3 letters, e.g., 'USA', 'GBR')
- `numeric_code` - ISO 3166-1 numeric code (e.g., '840' for USA)
- `name` - Official country name
- `native_name` - Name in native language(s) (JSON for multiple)
- `capital` - Capital city name
- `region` - Continent/region (e.g., 'Americas', 'Europe', 'Asia')
- `subregion` - More specific region (e.g., 'Northern America', 'Western Europe')
- `currency_code` - ISO 4217 currency code (e.g., 'USD', 'EUR')
- `currency_name` - Currency name (e.g., 'US Dollar')
- `currency_symbol` - Currency symbol (e.g., '$', '€')
- `phone_code` - International dialing code (e.g., '+1', '+44')
- `flag_emoji` - Unicode flag emoji (e.g., '🇺🇸', '🇬🇧')
- `flag_svg` - URL/path to SVG flag
- `languages` - JSON array of language codes (e.g., ['en', 'es'])
- `tld` - Top-level domain (e.g., '.us', '.uk')
- `latitude` - Country center latitude (decimal)
- `longitude` - Country center longitude (decimal)
- `is_active` - Boolean (for filtering)
- `is_eu_member` - Boolean (European Union membership)
- `display_order` - Integer (for sorting popular countries first)
- `metadata` - JSON (extra data like population, area, etc.)
- `created_at`
- `updated_at`

#### `timezones` (Reference table - Enhanced)
- `id` - Primary key
- `country_id` - Foreign key to countries (nullable for universal timezones like UTC)
- `name` - IANA timezone identifier (e.g., 'America/New_York', 'Europe/London')
- `abbreviation` - Current abbreviation (e.g., 'EST', 'GMT', 'CET')
- `abbreviation_dst` - DST abbreviation if applicable (e.g., 'EDT', 'BST')
- `offset` - Current UTC offset in seconds (e.g., -18000 for EST = -5 hours)
- `offset_dst` - DST offset in seconds if applicable
- `offset_formatted` - Human-readable offset (e.g., 'UTC-05:00', 'UTC+01:00')
- `uses_dst` - Boolean (whether this timezone observes daylight saving)
- `display_name` - Human-friendly name (e.g., 'Eastern Time (US & Canada)')
- `city_name` - Main city in this timezone (e.g., 'New York', 'London')
- `region` - Geographic region (e.g., 'America', 'Europe', 'Asia', 'Pacific')
- `coordinates` - JSON {lat, lng} (center of timezone)
- `population` - Approximate population in this timezone (optional, bigint)
- `is_primary` - Boolean (main timezone for the country)
- `is_active` - Boolean (for filtering deprecated timezones)
- `display_order` - Integer (for sorting common timezones first)
- `metadata` - JSON (extra data)
- `created_at`
- `updated_at`

#### `country_timezone` (Pivot table - Many-to-Many)
- `id` - Primary key
- `country_id` - Foreign key to countries
- `timezone_id` - Foreign key to timezones
- `is_primary` - Boolean (the main timezone for this country)
- `regions` - JSON array (which regions/states use this timezone, e.g., ['NY', 'FL', 'MA'])
- `notes` - Text (e.g., "Used in Alaska only", "Excluding Hawaii")
- `created_at`
- `updated_at`

### 2. Indexes & Constraints

#### Settings Table
- Unique index on `settings(key, scope, settable_type, settable_id)`
- Index on `settings(group, scope)`
- Index on `settings(settable_type, settable_id)`

#### Setting Lists Table
- Index on `setting_lists(key)`

#### Countries Table
- UNIQUE index on `countries(code)`
- UNIQUE index on `countries(code_alpha3)`
- Index on `countries(region, subregion)`
- Index on `countries(is_active)`
- Index on `countries(display_order)`

#### Timezones Table
- UNIQUE index on `timezones(name)`
- Index on `timezones(country_id)`
- Index on `timezones(region)`
- Index on `timezones(is_active, is_primary)`
- Index on `timezones(uses_dst)`

#### Country Timezone Pivot Table
- UNIQUE index on `country_timezone(country_id, timezone_id)`
- Index on `country_timezone(is_primary)`
- FOREIGN KEY `country_timezone(country_id)` REFERENCES `countries(id)` ON DELETE CASCADE
- FOREIGN KEY `country_timezone(timezone_id)` REFERENCES `timezones(id)` ON DELETE CASCADE

## Models & Relationships

### Setting Model
```php
- Polymorphic relationships:
  - settable() - morphTo() - User, Admin, etc.
  - referenceable() - morphTo() - Country, Timezone, SettingList, etc.

- Scopes:
  - scopeGlobal()
  - scopeForUser($user)
  - scopeByGroup($group)
  - scopePublic()

- Accessors/Mutators:
  - Cast 'value' based on 'type'
  - Encrypt/decrypt if 'is_encrypted' is true

- Methods:
  - getTypedValue() - Returns value cast to proper type
  - setTypedValue($value) - Sets and casts value
```

### SettingList Model
```php
- Relationships:
  - settings() - morphMany(Setting::class, 'referenceable')

- Scopes:
  - scopeByKey($key)
  - scopeActive()
```

### Country Model
```php
- Relationships:
  - timezones() - belongsToMany(Timezone::class, 'country_timezone')
    ->withPivot(['is_primary', 'regions', 'notes'])
    ->withTimestamps()
  - primaryTimezone() - belongsToMany with wherePivot('is_primary', true)
  - settings() - morphMany(Setting::class, 'referenceable')

- Scopes:
  - scopeActive($query) - where('is_active', true)
  - scopeByRegion($query, $region) - where('region', $region)
  - scopePopular($query) - orderBy('display_order')

- Accessors:
  - getNativeNamesAttribute() - Decode JSON to array
  - getLanguagesAttribute() - Decode JSON to array
  - getFullNameAttribute() - "{flag_emoji} {name}"

- Methods:
  - hasTimezone($timezoneId) - Check if country uses timezone
```

### Timezone Model
```php
- Relationships:
  - countries() - belongsToMany(Country::class, 'country_timezone')
    ->withPivot(['is_primary', 'regions', 'notes'])
    ->withTimestamps()
  - primaryCountry() - belongsToMany with wherePivot('is_primary', true)
  - settings() - morphMany(Setting::class, 'referenceable')

- Scopes:
  - scopeActive($query) - where('is_active', true)
  - scopeByCountry($query, $countryId) - whereHas('countries', ...)
  - scopePrimary($query) - where('is_primary', true)
  - scopeByRegion($query, $region) - where('region', $region)

- Accessors:
  - getCurrentOffsetAttribute() - Calculate offset considering DST
  - getCurrentAbbreviationAttribute() - Get abbreviation considering DST
  - getDisplayLabelAttribute() - "{display_name} ({offset_formatted})"
  - getCoordinatesAttribute() - Decode JSON to array

- Methods:
  - isCurrentlyDst() - Check if currently in DST
  - getOffsetInHours() - Convert seconds to hours
```

### User Model (extend existing)
```php
- Relationships:
  - settings() - morphMany(Setting::class, 'settable')

- Methods:
  - getSetting($key, $default = null)
  - setSetting($key, $value)
  - getSettingsByGroup($group)
```

## Service Layer

### SettingsService
```php
Methods:
- get($key, $default = null, $scope = 'global', $settableId = null)
- set($key, $value, $scope = 'global', $settableId = null)
- getByGroup($group, $scope = 'global', $settableId = null)
- getForUser(User $user, $group = null)
- setForUser(User $user, $key, $value)
- getAllByScope($scope)
- delete($key, $scope, $settableId = null)
- has($key, $scope, $settableId = null)
- getWithReference($key) - Get setting with loaded referenceable
- validateSetting($key, $value) - Validate against rules
- bulkSet(array $settings, $scope, $settableId = null)
```

## API Endpoints

### Settings Controller
```
GET    /api/settings - Get all accessible settings
GET    /api/settings/{group} - Get settings by group
GET    /api/settings/{key} - Get specific setting
POST   /api/settings - Create/update setting
PUT    /api/settings/{key} - Update specific setting
DELETE /api/settings/{key} - Delete setting

GET    /api/user/settings - Get current user's settings
PUT    /api/user/settings - Update user settings (bulk)
PUT    /api/user/settings/{key} - Update single user setting

GET    /api/admin/settings - Get admin settings (admin only)
PUT    /api/admin/settings - Update admin settings (admin only)

GET    /api/settings/groups - Get all setting groups with counts
GET    /api/settings/lists/{key} - Get predefined options from setting_lists
```

## Form Requests

### UpdateSettingRequest
- Validates based on setting type
- Applies validation_rules from database
- Checks permissions based on scope and is_public

### BulkUpdateSettingsRequest
- Validates multiple settings at once
- Ensures user has permission for each setting

## Configuration File

### config/settings.php
```php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Settings for New Users
    |--------------------------------------------------------------------------
    | These values are used by UserObserver to create default settings
    | when a new user is registered.
    */
    'defaults' => [
        'theme' => env('DEFAULT_THEME', 'default'),
        'language' => env('DEFAULT_LANGUAGE', 'en'),
        'timezone' => env('DEFAULT_TIMEZONE', 'UTC'),
        'items_per_page' => 25,
        'notifications_enabled' => true,
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
        'notifications' => [
            'name' => 'Notifications',
            'icon' => 'bell',
            'description' => 'Notification preferences',
            'order' => 4,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Available Setting Types
    |--------------------------------------------------------------------------
    */
    'types' => [
        'string', 'integer', 'boolean', 'array', 'json', 'reference'
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
```

## Frontend Implementation

### Vue Components

#### SettingsPage.vue (Main settings page)
- Tabbed interface by group
- Lists all settings in group
- Supports different input types based on setting type

#### SettingInput.vue (Dynamic input component)
- Renders appropriate input based on type:
  - Text/Number/Boolean inputs
  - Select dropdown (for references)
  - Searchable select (for countries, timezones)
  - Custom components for complex types

#### SettingGroup.vue
- Displays settings grouped together
- Shows group icon and description

#### UserSettingsPage.vue
- User-specific settings interface
- Filtered to only show user-accessible settings

### Pinia Store: settings.js
```javascript
State:
- settings: {} - All settings by key
- groups: [] - Available groups
- loading: false

Actions:
- fetchSettings(scope, group)
- fetchUserSettings()
- updateSetting(key, value)
- bulkUpdateSettings(settings)
- getSettingsByGroup(group)
```

### Service: settingsService.js
```javascript
Methods:
- getSettings(scope, group)
- getSetting(key)
- updateSetting(key, value)
- bulkUpdate(settings)
- getUserSettings()
- updateUserSettings(settings)
- getSettingLists(key)
- getCountries()
- getTimezones()
```

## Seeder Data

### SettingsSeeder
Seed default settings:
```
General Group:
- site_name (string)
- site_logo (string/file)
- maintenance_mode (boolean)

Localization Group:
- default_timezone (reference -> Timezone)
- default_country (reference -> Country)
- date_format (reference -> SettingList)
- time_format (reference -> SettingList)
- default_language (reference -> SettingList)

Appearance Group:
- user_theme (reference -> SettingList with key 'themes') - User's selected theme
- items_per_page (integer)
- default_theme (string) - Global default theme for new users

Notifications Group:
- email_notifications (boolean)
- push_notifications (boolean)
```

### SettingListsSeeder
Seed predefined options for various settings.

**Example Data Structure:**
```php
[
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

    // Application Themes (from config/themes.php)
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
]
```

**Implementation Notes:**
- All themes from `config/themes.php` should be automatically synced to `setting_lists` table
- Theme metadata includes color codes for preview display in UI
- Can add/remove themes dynamically through admin interface

### CountriesSeeder
Seed comprehensive country data with all ISO codes, currencies, and metadata.

**Example Data Structure:**
```php
[
    // United States
    [
        'code' => 'US',
        'code_alpha3' => 'USA',
        'numeric_code' => '840',
        'name' => 'United States',
        'native_name' => json_encode(['eng' => 'United States']),
        'capital' => 'Washington, D.C.',
        'region' => 'Americas',
        'subregion' => 'Northern America',
        'currency_code' => 'USD',
        'currency_name' => 'US Dollar',
        'currency_symbol' => '$',
        'phone_code' => '+1',
        'flag_emoji' => '🇺🇸',
        'flag_svg' => '/flags/us.svg',
        'languages' => json_encode(['en']),
        'tld' => '.us',
        'latitude' => 37.0902,
        'longitude' => -95.7129,
        'is_active' => true,
        'is_eu_member' => false,
        'display_order' => 1,
        'metadata' => json_encode([
            'population' => 331002651,
            'area' => 9833517,
        ]),
    ],
    // ... more countries (approx 195+ countries)
]
```

**Data Sources:**
- Use packages like `webpatser/laravel-countries` or REST Countries API
- Include all 195+ UN recognized countries
- Set popular countries (US, GB, CA, AU, etc.) with lower display_order
- Mark EU member states with is_eu_member = true

### TimezonesSeeder
Seed all IANA timezones with comprehensive details and country relationships.

**Example Data Structure:**
```php
[
    // Eastern Time (US & Canada)
    [
        'name' => 'America/New_York',
        'abbreviation' => 'EST',
        'abbreviation_dst' => 'EDT',
        'offset' => -18000, // -5 hours in seconds
        'offset_dst' => -14400, // -4 hours in seconds
        'offset_formatted' => 'UTC-05:00',
        'uses_dst' => true,
        'display_name' => 'Eastern Time (US & Canada)',
        'city_name' => 'New York',
        'region' => 'America',
        'coordinates' => json_encode(['lat' => 40.7128, 'lng' => -74.0060]),
        'population' => 50000000,
        'is_primary' => true,
        'is_active' => true,
        'display_order' => 1,
    ],
    // ... more timezones (approx 400+ IANA timezones)
]
```

**Pivot Table Seeding (Country-Timezone relationships):**
```php
// United States has multiple timezones
[
    ['country_code' => 'US', 'timezone_name' => 'America/New_York', 'is_primary' => true, 'regions' => ['NY', 'FL', 'MA', 'PA']],
    ['country_code' => 'US', 'timezone_name' => 'America/Chicago', 'is_primary' => false, 'regions' => ['IL', 'TX', 'MO']],
    ['country_code' => 'US', 'timezone_name' => 'America/Denver', 'is_primary' => false, 'regions' => ['CO', 'MT', 'UT']],
    ['country_code' => 'US', 'timezone_name' => 'America/Los_Angeles', 'is_primary' => false, 'regions' => ['CA', 'WA', 'NV']],
    ['country_code' => 'US', 'timezone_name' => 'America/Anchorage', 'is_primary' => false, 'regions' => ['AK']],
    ['country_code' => 'US', 'timezone_name' => 'Pacific/Honolulu', 'is_primary' => false, 'regions' => ['HI']],
]

// France has single timezone
[
    ['country_code' => 'FR', 'timezone_name' => 'Europe/Paris', 'is_primary' => true, 'regions' => null],
]

// Australia has multiple timezones
[
    ['country_code' => 'AU', 'timezone_name' => 'Australia/Sydney', 'is_primary' => true, 'regions' => ['NSW', 'VIC', 'TAS']],
    ['country_code' => 'AU', 'timezone_name' => 'Australia/Brisbane', 'is_primary' => false, 'regions' => ['QLD']],
    ['country_code' => 'AU', 'timezone_name' => 'Australia/Adelaide', 'is_primary' => false, 'regions' => ['SA']],
    ['country_code' => 'AU', 'timezone_name' => 'Australia/Perth', 'is_primary' => false, 'regions' => ['WA']],
    ['country_code' => 'AU', 'timezone_name' => 'Australia/Darwin', 'is_primary' => false, 'regions' => ['NT']],
]
```

**Data Sources:**
- PHP's built-in `DateTimeZone::listIdentifiers()`
- IANA Time Zone Database
- Package: `nesbot/carbon` for offset calculations
- Package: `camroncade/timezone` for display names
- Set common timezones (America/New_York, Europe/London, etc.) with lower display_order

**Seeder Implementation Order:**
1. Seed Countries first
2. Seed Timezones second
3. Seed Country-Timezone pivot relationships last (using country codes and timezone names to find IDs)

## Testing Strategy

### Unit Tests
- SettingsService methods
- Setting model accessors/mutators
- Type casting logic
- Encryption/decryption

### Feature Tests
- GET /api/settings (various scopes)
- POST/PUT settings APIs
- User settings CRUD
- Admin settings CRUD (authorization)
- Permission checks
- Validation rules enforcement

## Key Features

1. **Flexible Type System**: Support for various data types with automatic casting
2. **Polymorphic References**: Settings can reference any model (Country, Timezone, SettingList, custom models)
3. **Scope-based Access**: Global, user-specific, admin-specific settings
4. **Group Organization**: Settings organized into logical groups with icons
5. **Validation**: Database-stored validation rules for each setting
6. **Encryption**: Sensitive settings can be encrypted
7. **Caching**: Cache frequently accessed settings for performance
8. **UI Metadata**: Icons, labels, descriptions for rich UI
9. **Bulk Operations**: Update multiple settings at once
10. **Extensible**: Easy to add new reference tables or setting types

## File Structure

```
Backend:
├── app/Models/
│   ├── Setting.php
│   ├── SettingList.php
│   ├── Country.php
│   └── Timezone.php
├── app/Observers/
│   └── UserObserver.php (auto-create default settings on user registration)
├── app/Services/
│   └── SettingsService.php
├── app/Http/Controllers/Api/
│   ├── SettingsController.php
│   ├── UserSettingsController.php
│   ├── CountriesController.php (get countries with timezones)
│   └── TimezonesController.php (get timezones by region/country)
├── app/Http/Requests/
│   ├── UpdateSettingRequest.php
│   └── BulkUpdateSettingsRequest.php
├── app/Console/Commands/
│   └── MigrateUserThemesToSettings.php (theme migration command)
├── database/migrations/
│   ├── create_settings_table.php
│   ├── create_setting_lists_table.php
│   ├── create_countries_table.php
│   ├── create_timezones_table.php
│   └── create_country_timezone_table.php (pivot)
├── database/seeders/
│   ├── SettingsSeeder.php
│   ├── SettingListsSeeder.php
│   ├── CountriesSeeder.php (195+ countries with full ISO data)
│   ├── TimezonesSeeder.php (400+ IANA timezones)
│   └── CountryTimezoneSeeder.php (pivot relationships)
├── config/
│   └── settings.php
└── tests/Feature/
    ├── SettingsTest.php
    └── UserSettingsTest.php

Frontend:
├── resources/js/pages/settings/
│   ├── SettingsPage.vue
│   └── UserSettingsPage.vue
├── resources/js/components/settings/
│   ├── SettingInput.vue
│   ├── SettingGroup.vue
│   └── SettingsForm.vue
├── resources/js/stores/
│   └── settings.js
└── resources/js/services/
    └── settingsService.js
```

## Migration from Current Theme System

The application currently uses a `theme` column in the `users` table for theme management. This needs to be migrated to the new settings system.

### Current Implementation (To Be Removed)
```php
// users table
- theme column (string)

// User model
- fillable: ['theme']

// AuthController
- updateTheme() method with validation

// API routes
- PUT /api/theme

// Frontend
- ThemeSwitcher.vue component
- theme.js Pinia store
- authService.updateTheme()
- auth.js store updateTheme()
```

### New Implementation (Settings-Based)
```php
// settings table (user scope)
- key: 'user_theme'
- value: theme name (e.g., 'default', 'ocean')
- type: 'reference'
- referenceable_type: 'App\Models\SettingList'
- referenceable_id: ID of theme in setting_lists
- settable_type: 'App\Models\User'
- settable_id: user ID

// API routes (NEW)
- GET /api/user/settings/theme - Get user's theme setting
- PUT /api/user/settings/theme - Update user's theme (via settings system)
- DELETE old route: PUT /api/theme

// Frontend (UPDATED)
- Fetch theme from settings API instead of user resource
- Update theme via settings API
- ThemeSwitcher.vue updated to use settings store
```

### Migration Steps

#### Backend Migration Tasks
1. **Create data migration script**
   - Create command: `php artisan make:command MigrateUserThemesToSettings`
   - Read all users with non-null theme column
   - For each user, create a setting record:
     ```php
     Setting::create([
         'key' => 'user_theme',
         'value' => json_encode($user->theme),
         'type' => 'reference',
         'group' => 'appearance',
         'scope' => 'user',
         'settable_type' => 'App\\Models\\User',
         'settable_id' => $user->id,
         'referenceable_type' => 'App\\Models\\SettingList',
         'referenceable_id' => SettingList::where('key', 'themes')->where('value', $user->theme)->first()->id,
         // ... other fields
     ]);
     ```

2. **Remove old theme endpoints**
   - Delete `AuthController::updateTheme()` method
   - Remove route `PUT /api/theme` from `routes/api.php`
   - Remove `'theme'` from User model's `$fillable` array

3. **Create migration to drop theme column** (after data migration)
   - Create: `php artisan make:migration drop_theme_column_from_users_table`
   - `$table->dropColumn('theme');`
   - **Run ONLY after data migration is complete and tested**

4. **Update UserResource**
   - Remove `'theme' => $this->theme` line
   - Theme will now be fetched separately via settings API

#### Frontend Migration Tasks
1. **Update theme loading in spa.js**
   - Change from fetching user.theme to fetching from settings API
   - Load theme from `GET /api/user/settings/theme` or `GET /api/user/settings` filtered by group='appearance'

2. **Update ThemeSwitcher.vue**
   - Change API calls from `authService.updateTheme()` to `settingsService.updateSetting('user_theme', value)`
   - Fetch themes list from `GET /api/settings/lists/themes` instead of hardcoded

3. **Remove old theme code**
   - Delete `authService.updateTheme()` method
   - Delete `auth.js store.updateTheme()` method
   - Remove theme-related code from auth store

4. **Update theme store**
   - Integrate with settings store instead of auth store
   - Fetch theme from settings API on initialization

### Migration Rollback Plan
If migration fails, rollback procedure:
1. Keep `theme` column in users table
2. Keep old API endpoints active
3. Revert frontend to use old theme system
4. Delete orphaned settings records

### Testing Migration
1. Test that all users' themes are correctly migrated to settings
2. Test theme switching through new settings API
3. Verify theme persistence across sessions
4. Test that deleted users don't leave orphaned setting records (cascade delete)
5. Verify performance with large user base

## Implementation Order

### Phase 1: Database & Models
1. Create database migrations with embedded seeders
   - `create_settings_table.php` (main settings table)
   - `create_setting_lists_table.php` (predefined options)
   - `create_countries_table.php` (enhanced with all ISO fields)
   - `create_timezones_table.php` (enhanced with DST, regions, etc.)
   - `create_country_timezone_table.php` (many-to-many pivot)

   **IMPORTANT:** Each migration's `up()` method should call its respective seeder:
   ```php
   public function up()
   {
       Schema::create('setting_lists', function (Blueprint $table) {
           // ... table definition
       });

       // Seed immediately after table creation for production deployments
       Artisan::call('db:seed', [
           '--class' => 'SettingListsSeeder',
           '--force' => true,
       ]);
   }
   ```

   This ensures reference data is available in production without manual seeding.

2. Create models with relationships and scopes
   - `Setting.php` (with polymorphic relationships)
   - `SettingList.php`
   - `Country.php` (with timezone relationship and accessors)
   - `Timezone.php` (with country relationship and DST logic)
   - Update `User.php` (add settings relationship and helper methods)

3. Create User Event Observer for default settings
   - Create `app/Observers/UserObserver.php`
   - Listen to `created` event on User model
   - Automatically create default user-level settings when new user is registered
   - Register observer in `AppServiceProvider` or `EventServiceProvider`

   **Example UserObserver:**
   ```php
   namespace App\Observers;

   use App\Models\User;
   use App\Models\Setting;
   use App\Models\SettingList;

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
                   'settable_type' => 'App\\Models\\User',
                   'settable_id' => $user->id,
                   'referenceable_type' => 'App\\Models\\SettingList',
                   'referenceable_id' => $themeList?->id,
                   'order' => 1,
               ],
               // Add more default settings here (language, timezone, etc.)
           ];

           foreach ($defaultSettings as $settingData) {
               Setting::create($settingData);
           }
       }

       public function deleting(User $user): void
       {
           // Clean up user settings when user is deleted
           $user->settings()->delete();
       }
   }
   ```

   This ensures every new user starts with sensible default settings.

### Phase 2: Seeders
4. Create comprehensive seeders with real-world data
   - `SettingListsSeeder.php` (date formats, time formats, languages, **all 9 themes**)
   - `CountriesSeeder.php` (195+ countries with full ISO 3166 data, currencies, flags)
   - `TimezonesSeeder.php` (400+ IANA timezones with offsets, DST info)
   - `CountryTimezoneSeeder.php` (pivot table relationships for multi-timezone countries)
   - `SettingsSeeder.php` (default global settings including default_theme, referencing seeded data)

### Phase 3: Business Logic
5. Create SettingsService with core logic
   - CRUD operations for all scopes (global, user, admin)
   - Type casting and validation
   - Encryption/decryption for sensitive settings
   - Reference resolution (eager load related models)
   - Caching layer

### Phase 4: API Layer
6. Create API controllers and routes
   - `SettingsController.php` (global and admin settings)
   - `UserSettingsController.php` (user-specific settings)
   - `CountriesController.php` (get countries with timezones)
   - `TimezonesController.php` (get timezones by country/region)
   - Define routes in `routes/api.php`

7. Create form requests for validation
   - `UpdateSettingRequest.php` (validates based on type and rules)
   - `BulkUpdateSettingsRequest.php` (batch updates)

### Phase 5: Testing
8. Create comprehensive tests
   - Unit tests for SettingsService methods
   - Unit tests for model accessors/mutators (DST calculation, etc.)
   - Feature tests for all API endpoints
   - Test permission checks (scope-based access)
   - Test country-timezone relationships
   - Test validation rules enforcement
   - **Test UserObserver:**
     - Test that default settings are created when new user registers
     - Test that user settings are deleted when user is deleted
     - Test that correct theme reference is created
     - Test multiple concurrent user registrations

### Phase 6: Frontend Service Layer
9. Create frontend service layer
   - `settingsService.js` (API calls for settings CRUD)
   - `countriesService.js` (fetch countries with timezones)
   - `timezonesService.js` (fetch timezones)

### Phase 7: State Management
10. Create Pinia stores
   - `stores/settings.js` (settings state and actions)
   - `stores/countries.js` (countries cache)
   - `stores/timezones.js` (timezones cache)

### Phase 8: UI Components
11. Create Vue components
    - `SettingInput.vue` (dynamic input based on type)
    - `SettingGroup.vue` (grouped settings display)
    - `SettingsForm.vue` (form wrapper with validation)
    - `CountrySelect.vue` (searchable country dropdown)
    - `TimezoneSelect.vue` (searchable timezone dropdown with offset display)
    - `SettingsPage.vue` (main settings page with tabs)
    - `UserSettingsPage.vue` (user-specific settings)

### Phase 9: Routing & Integration
12. Add settings pages to router
    - Add routes for settings pages
    - Add navigation links to sidebar/menu
    - Set up route guards for admin-only settings

### Phase 10: Theme System Migration
13. Migrate from users.theme to settings system
    - Create Artisan command `MigrateUserThemesToSettings`
    - Run migration to copy user themes to settings table
    - Test data integrity and rollback capability
    - Remove old theme-related code (backend & frontend)
    - Create migration to drop `theme` column from users table
    - Update frontend to use settings API for themes

### Phase 11: Final Testing
14. End-to-end testing
    - Test all setting types (string, integer, boolean, reference)
    - Test theme switching via settings (all 9 themes)
    - Test country-timezone selection flow
    - Test user vs admin vs global scope permissions
    - Test settings persistence and retrieval
    - Test validation and error handling
    - Performance testing with caching
    - Test theme migration and backward compatibility

---

## Production Deployment Considerations

### 1. Seeders in Migrations (Critical for Production)
**Problem:** In production, developers often run `php artisan migrate` but forget to run `php artisan db:seed`, resulting in missing reference data.

**Solution:** Call seeders directly from migration files:

```php
// Example: create_setting_lists_table.php
public function up(): void
{
    Schema::create('setting_lists', function (Blueprint $table) {
        $table->id();
        $table->string('key')->index();
        $table->string('label');
        $table->string('value');
        $table->json('metadata')->nullable();
        $table->boolean('is_active')->default(true);
        $table->integer('order')->default(0);
        $table->timestamps();
    });

    // Seed immediately after table creation
    Artisan::call('db:seed', [
        '--class' => 'SettingListsSeeder',
        '--force' => true, // Required for production
    ]);
}

public function down(): void
{
    Schema::dropIfExists('setting_lists');
}
```

**Tables that MUST be seeded in migrations:**
- `setting_lists` → SettingListsSeeder (themes, date formats, time formats, languages)
- `countries` → CountriesSeeder (195+ countries)
- `timezones` → TimezonesSeeder (400+ timezones)
- `country_timezone` → CountryTimezoneSeeder (pivot relationships)
- `settings` → SettingsSeeder (global default settings)

**Benefits:**
- ✅ Single command deployment: `php artisan migrate`
- ✅ Guaranteed data availability in production
- ✅ Prevents "reference not found" errors
- ✅ Idempotent: Running migration multiple times won't duplicate data (seeders should handle this)

**Seeder Best Practices:**
```php
// In each seeder, use updateOrCreate to prevent duplicates
public function run(): void
{
    $themes = [/* ... theme data ... */];

    foreach ($themes as $theme) {
        SettingList::updateOrCreate(
            ['key' => 'themes', 'value' => $theme['value']],
            $theme
        );
    }
}
```

### 2. Auto-Create Default User Settings on Registration
**Problem:** New users have no settings, requiring manual initialization or complex null checks throughout the application.

**Solution:** Use Laravel Model Observer to automatically create default settings when user is created.

**Implementation:**
1. Create `app/Observers/UserObserver.php` (see Phase 1, step 3 for full code)
2. Register observer in `AppServiceProvider::boot()`:
   ```php
   use App\Models\User;
   use App\Observers\UserObserver;

   public function boot(): void
   {
       User::observe(UserObserver::class);
   }
   ```

**Default Settings to Create:**
- `user_theme` → Reference to default theme from SettingList
- `user_language` → Reference to default language (optional)
- `user_timezone` → Reference to default timezone (optional)
- `notifications_enabled` → Boolean (default true)

**Benefits:**
- ✅ Every user starts with sensible defaults
- ✅ No null checks needed in UI
- ✅ Consistent user experience
- ✅ Settings can be customized immediately after registration
- ✅ Automatic cleanup when user is deleted (cascade)

**Configuration:**
Create `config/settings.php` with default values:
```php
return [
    'defaults' => [
        'theme' => env('DEFAULT_THEME', 'default'),
        'language' => env('DEFAULT_LANGUAGE', 'en'),
        'timezone' => env('DEFAULT_TIMEZONE', 'UTC'),
        'items_per_page' => 25,
    ],
    // ... other settings config
];
```

---

## Summary of Key Changes for Theme Integration

### 1. Themes in SettingLists Table
- All 9 themes (default, ocean, sunset, forest, midnight, crimson, amber, slate, lavender) seeded in `setting_lists` table with key='themes'
- Each theme includes metadata: description, primary color, secondary color, preview gradient
- Themes can be managed dynamically through admin interface

### 2. User Theme as Setting
- Theme stored in `settings` table instead of `users.theme` column
- Setting key: `user_theme`
- Setting type: `reference` pointing to SettingList
- Setting scope: `user` (user-specific)
- Polymorphic relationship: `settable_type` = User, `settable_id` = user ID

### 3. API Changes
**Removed:**
- `PUT /api/theme` (from AuthController)
- `AuthController::updateTheme()` method
- `UserResource` theme field

**Added:**
- `GET /api/settings/lists/themes` - Get all available themes
- `GET /api/user/settings/theme` - Get user's selected theme
- `PUT /api/user/settings/theme` - Update user's theme (via settings system)
- `PUT /api/user/settings` - Bulk update including theme

### 4. Frontend Changes
**Updated:**
- `ThemeSwitcher.vue` - Fetch themes from settings API, update via settings API
- `stores/theme.js` - Integrate with settings store instead of auth store
- `spa.js` - Load theme from settings API on boot

**Removed:**
- `authService.updateTheme()` method
- `auth.js store.updateTheme()` method
- Theme field from user resource handling

### 5. Data Migration
- Artisan command `MigrateUserThemesToSettings` to migrate existing user themes
- After migration and testing: Drop `theme` column from `users` table
- Migration to remove `add_theme_column_to_users_table.php` (reverse the original migration)

### 6. Benefits of New Approach
- **Centralized Management**: All settings in one place
- **Flexible**: Easy to add new themes without code changes
- **Auditable**: Track theme changes through settings history
- **Extensible**: Can add theme-related metadata (last_changed, theme_history, etc.)
- **Consistent**: Uses same architecture as other user preferences
- **Polymorphic**: Can have global default theme + user overrides
- **UI Improvements**: Theme picker can show previews from metadata
