<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Setting;
use App\Models\SettingList;
use App\Models\Timezone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SettingsEdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_setting_supports_encryption_flag(): void
    {
        $user = User::factory()->create();

        // Verify that the is_encrypted flag exists and can be set
        $setting = Setting::create([
            'key' => 'encrypted_api_key',
            'value' => json_encode('test_value'),
            'type' => 'string',
            'group' => 'general',
            'scope' => 'user',
            'label' => 'Encrypted API Key',
            'is_public' => false,
            'is_encrypted' => true,
            'settable_type' => User::class,
            'settable_id' => $user->id,
        ]);

        // Verify the flag is stored correctly
        $this->assertTrue($setting->is_encrypted);
        $this->assertDatabaseHas('settings', [
            'key' => 'encrypted_api_key',
            'is_encrypted' => true,
        ]);
    }

    public function test_encrypted_setting_returns_null_on_decryption_failure(): void
    {
        $user = User::factory()->create();

        // Create a setting with invalid encrypted data
        $setting = Setting::create([
            'key' => 'corrupted_encrypted_key',
            'value' => 'invalid_encrypted_data_not_base64',
            'type' => 'string',
            'group' => 'general',
            'scope' => 'user',
            'label' => 'Corrupted Key',
            'is_public' => false,
            'is_encrypted' => true,
            'settable_type' => User::class,
            'settable_id' => $user->id,
        ]);

        // Should return null on decryption failure
        $this->assertNull($setting->getTypedValue());
    }

    public function test_cache_is_cleared_when_setting_is_updated(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        config(['admin.id' => [$user->id]]);

        // Enable caching
        config(['settings.cache.enabled' => true]);

        // Create and cache a setting
        $setting = Setting::create([
            'key' => 'cached_setting',
            'value' => json_encode('initial_value'),
            'type' => 'string',
            'group' => 'general',
            'scope' => 'global',
            'label' => 'Cached Setting',
            'is_public' => true,
        ]);

        // Fetch it to populate cache
        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.settings.show', 'cached_setting'));

        $response->assertStatus(200)
            ->assertJsonPath('setting.value', 'initial_value');

        // Update the setting
        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.settings.update', 'cached_setting'), [
                'value' => 'updated_value',
            ]);

        // Verify cache was cleared and new value is returned
        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.settings.show', 'cached_setting'));

        $response->assertStatus(200)
            ->assertJsonPath('setting.value', 'updated_value');
    }

    public function test_cache_is_cleared_when_setting_is_deleted(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        config(['admin.id' => [$user->id]]);

        // Enable caching
        config(['settings.cache.enabled' => true]);

        // Create a setting
        $setting = Setting::create([
            'key' => 'deletable_cached_setting',
            'value' => json_encode('test_value'),
            'type' => 'string',
            'group' => 'general',
            'scope' => 'global',
            'label' => 'Deletable Cached Setting',
            'is_public' => true,
        ]);

        // Fetch to cache it
        $this->actingAs($user, 'sanctum')
            ->getJson(route('api.settings.show', 'deletable_cached_setting'));

        // Delete the setting
        $this->actingAs($user, 'sanctum')
            ->deleteJson(route('api.settings.destroy', 'deletable_cached_setting'));

        // Verify it's deleted and cache was cleared
        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.settings.show', 'deletable_cached_setting'));

        $response->assertStatus(404);
    }

    public function test_setting_with_reference_to_setting_list(): void
    {
        $user = User::factory()->create();

        // Get a theme from setting_lists
        $theme = SettingList::where('key', 'themes')
            ->where('value', 'ocean')
            ->first();

        $this->assertNotNull($theme);

        // Create a setting that references this theme
        $setting = Setting::create([
            'key' => 'referenced_theme',
            'value' => json_encode('ocean'),
            'type' => 'reference',
            'group' => 'appearance',
            'scope' => 'user',
            'label' => 'Referenced Theme',
            'is_public' => true,
            'settable_type' => User::class,
            'settable_id' => $user->id,
            'referenceable_type' => SettingList::class,
            'referenceable_id' => $theme->id,
        ]);

        // Fetch with reference
        $settingWithRef = Setting::with('referenceable')
            ->where('key', 'referenced_theme')
            ->first();

        $this->assertNotNull($settingWithRef->referenceable);
        $this->assertEquals('ocean', $settingWithRef->referenceable->value);
    }

    public function test_setting_with_reference_to_country(): void
    {
        $user = User::factory()->create();

        // Get a country
        $country = Country::where('code', 'US')->first();
        $this->assertNotNull($country);

        // Create a setting that references this country
        $setting = Setting::create([
            'key' => 'user_country',
            'value' => json_encode('US'),
            'type' => 'reference',
            'group' => 'localization',
            'scope' => 'user',
            'label' => 'User Country',
            'is_public' => true,
            'settable_type' => User::class,
            'settable_id' => $user->id,
            'referenceable_type' => Country::class,
            'referenceable_id' => $country->id,
        ]);

        // Fetch with reference
        $settingWithRef = Setting::with('referenceable')
            ->where('key', 'user_country')
            ->first();

        $this->assertNotNull($settingWithRef->referenceable);
        $this->assertEquals('US', $settingWithRef->referenceable->code);
    }

    public function test_setting_with_reference_to_timezone(): void
    {
        $user = User::factory()->create();

        // Get a timezone
        $timezone = Timezone::where('name', 'America/New_York')->first();
        $this->assertNotNull($timezone);

        // Create a setting that references this timezone
        $setting = Setting::create([
            'key' => 'user_timezone',
            'value' => json_encode('America/New_York'),
            'type' => 'reference',
            'group' => 'localization',
            'scope' => 'user',
            'label' => 'User Timezone',
            'is_public' => true,
            'settable_type' => User::class,
            'settable_id' => $user->id,
            'referenceable_type' => Timezone::class,
            'referenceable_id' => $timezone->id,
        ]);

        // Fetch with reference
        $settingWithRef = Setting::with('referenceable')
            ->where('key', 'user_timezone')
            ->first();

        $this->assertNotNull($settingWithRef->referenceable);
        $this->assertEquals('America/New_York', $settingWithRef->referenceable->name);
    }

    public function test_timezone_dst_detection(): void
    {
        $timezone = Timezone::where('name', 'America/New_York')->first();
        $this->assertNotNull($timezone);

        // America/New_York uses DST
        $this->assertTrue($timezone->uses_dst);

        // Method should work without errors
        $isDst = $timezone->isCurrentlyDst();
        $this->assertIsBool($isDst);
    }

    public function test_timezone_without_dst(): void
    {
        $timezone = Timezone::where('name', 'Asia/Tokyo')->first();
        $this->assertNotNull($timezone);

        // Tokyo doesn't use DST
        $this->assertFalse($timezone->uses_dst);
        $this->assertFalse($timezone->isCurrentlyDst());
    }

    public function test_timezone_current_offset_calculation(): void
    {
        $timezone = Timezone::where('name', 'America/New_York')->first();
        $this->assertNotNull($timezone);

        // Should calculate current offset
        $currentOffset = $timezone->current_offset;
        $this->assertIsInt($currentOffset);

        // Should be either standard offset or DST offset
        $this->assertTrue(
            $currentOffset === $timezone->offset || $currentOffset === $timezone->offset_dst
        );
    }

    public function test_timezone_offset_formatted(): void
    {
        $timezone = Timezone::where('name', 'America/New_York')->first();
        $this->assertNotNull($timezone);

        $formatted = $timezone->offset_formatted;
        $this->assertIsString($formatted);
        // Format can be "UTC±HH:MM" or "±HH:MM"
        $this->assertMatchesRegularExpression('/^(UTC)?[+-]\d{2}:\d{2}$/', $formatted);
    }

    public function test_country_fullname_with_flag(): void
    {
        $country = Country::where('code', 'US')->first();
        $this->assertNotNull($country);

        $fullName = $country->full_name;
        $this->assertStringContainsString($country->name, $fullName);

        // Should include flag emoji if present
        if ($country->flag_emoji) {
            $this->assertStringContainsString($country->flag_emoji, $fullName);
        }
    }

    public function test_country_timezone_relationship_with_regions(): void
    {
        $country = Country::where('code', 'US')->first();
        $this->assertNotNull($country);

        // US should have multiple timezones
        $timezones = $country->timezones;
        $this->assertGreaterThan(0, $timezones->count());

        // Check for primary timezone
        $primaryTimezone = $timezones->where('pivot.is_primary', true)->first();
        $this->assertNotNull($primaryTimezone);
    }

    public function test_timezone_country_relationship(): void
    {
        $timezone = Timezone::where('name', 'America/New_York')->first();
        $this->assertNotNull($timezone);

        // Should have countries
        $countries = $timezone->countries;
        $this->assertGreaterThan(0, $countries->count());
    }

    public function test_setting_array_type_casting(): void
    {
        $user = User::factory()->create();

        $arrayValue = ['option1', 'option2', 'option3'];

        $setting = Setting::create([
            'key' => 'array_setting',
            'value' => json_encode($arrayValue),
            'type' => 'array',
            'group' => 'general',
            'scope' => 'user',
            'label' => 'Array Setting',
            'is_public' => true,
            'settable_type' => User::class,
            'settable_id' => $user->id,
        ]);

        $typedValue = $setting->getTypedValue();
        $this->assertIsArray($typedValue);
        $this->assertEquals($arrayValue, $typedValue);
    }

    public function test_setting_json_type_casting(): void
    {
        $user = User::factory()->create();

        $jsonValue = ['key1' => 'value1', 'key2' => 'value2'];

        $setting = Setting::create([
            'key' => 'json_setting',
            'value' => json_encode($jsonValue),
            'type' => 'json',
            'group' => 'general',
            'scope' => 'user',
            'label' => 'JSON Setting',
            'is_public' => true,
            'settable_type' => User::class,
            'settable_id' => $user->id,
        ]);

        $typedValue = $setting->getTypedValue();
        $this->assertIsArray($typedValue);
        $this->assertEquals($jsonValue, $typedValue);
    }

    public function test_multiple_users_can_have_same_setting_key(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        // All should have user_theme from observer
        $settings = Setting::where('key', 'user_theme')
            ->where('scope', 'user')
            ->get();

        $this->assertGreaterThanOrEqual(3, $settings->count());

        // Each user should have exactly one user_theme setting
        $user1Settings = $settings->where('settable_id', $user1->id);
        $user2Settings = $settings->where('settable_id', $user2->id);
        $user3Settings = $settings->where('settable_id', $user3->id);

        $this->assertCount(1, $user1Settings);
        $this->assertCount(1, $user2Settings);
        $this->assertCount(1, $user3Settings);
    }

    public function test_setting_unique_constraint_works(): void
    {
        $user = User::factory()->create();

        // Try to create duplicate setting with same key, scope, settable_type, settable_id
        Setting::create([
            'key' => 'duplicate_test',
            'value' => json_encode('value1'),
            'type' => 'string',
            'group' => 'general',
            'scope' => 'user',
            'label' => 'Duplicate Test 1',
            'is_public' => true,
            'settable_type' => User::class,
            'settable_id' => $user->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Setting::create([
            'key' => 'duplicate_test',
            'value' => json_encode('value2'),
            'type' => 'string',
            'group' => 'general',
            'scope' => 'user',
            'label' => 'Duplicate Test 2',
            'is_public' => true,
            'settable_type' => User::class,
            'settable_id' => $user->id,
        ]);
    }
}
