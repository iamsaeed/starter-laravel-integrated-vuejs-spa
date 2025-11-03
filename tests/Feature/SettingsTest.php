<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\SettingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Create a whitelisted admin user for testing admin-only endpoints.
     */
    protected function createWhitelistedAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        config(['admin.id' => [$admin->id]]);

        return $admin;
    }

    public function test_user_can_get_their_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.user.settings.index'));

        $response->assertStatus(200)
            ->assertJsonStructure(['settings']);
    }

    public function test_user_can_update_single_setting(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson(route('api.user.settings.update-single', 'user_theme'), [
                'value' => 'ocean',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Setting updated successfully.',
            ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'user_theme',
            'settable_type' => User::class,
            'settable_id' => $user->id,
        ]);
    }

    public function test_user_can_bulk_update_settings(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson(route('api.user.settings.update'), [
                'settings' => [
                    'user_theme' => 'sunset',
                    'items_per_page' => 50,
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Settings updated successfully.',
            ]);
    }

    public function test_can_get_all_global_settings(): void
    {
        $admin = $this->createWhitelistedAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson(route('api.settings.index'));

        $response->assertStatus(200)
            ->assertJsonStructure(['settings']);
    }

    public function test_can_get_setting_groups(): void
    {
        $admin = $this->createWhitelistedAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson(route('api.settings.groups'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'groups' => [
                    '*' => ['key', 'name', 'icon', 'description', 'order', 'count'],
                ],
            ]);
    }

    public function test_can_get_setting_lists(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.settings.lists', 'themes'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'lists' => [
                    '*' => ['id', 'key', 'label', 'value', 'metadata'],
                ],
            ]);
    }

    public function test_can_get_countries(): void
    {
        $response = $this->getJson(route('api.countries.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'countries' => [
                    '*' => ['id', 'code', 'name', 'flag_emoji'],
                ],
            ]);
    }

    public function test_can_get_timezones(): void
    {
        $response = $this->getJson(route('api.timezones.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'timezones' => [
                    '*' => ['id', 'name', 'display_name', 'offset_formatted'],
                ],
            ]);
    }

    public function test_user_observer_creates_default_settings(): void
    {
        $user = User::factory()->create();

        // Verify default settings were created
        $this->assertDatabaseHas('settings', [
            'key' => 'user_theme',
            'settable_type' => User::class,
            'settable_id' => $user->id,
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'dark_mode',
            'settable_type' => User::class,
            'settable_id' => $user->id,
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'items_per_page',
            'settable_type' => User::class,
            'settable_id' => $user->id,
        ]);
    }

    public function test_global_settings_exist_after_seeding(): void
    {
        $this->assertDatabaseHas('settings', [
            'key' => 'site_name',
            'scope' => 'global',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'default_theme',
            'scope' => 'global',
        ]);
    }

    public function test_themes_are_seeded(): void
    {
        $themes = SettingList::where('key', 'themes')->get();

        $this->assertGreaterThanOrEqual(9, $themes->count());
        $this->assertTrue($themes->contains('value', 'default'));
        $this->assertTrue($themes->contains('value', 'ocean'));
        $this->assertTrue($themes->contains('value', 'sunset'));
    }

    public function test_countries_are_seeded(): void
    {
        $this->assertDatabaseHas('countries', ['code' => 'US']);
        $this->assertDatabaseHas('countries', ['code' => 'GB']);
        $this->assertDatabaseHas('countries', ['code' => 'CA']);
    }

    public function test_timezones_are_seeded(): void
    {
        $this->assertDatabaseHas('timezones', ['name' => 'America/New_York']);
        $this->assertDatabaseHas('timezones', ['name' => 'Europe/London']);
        $this->assertDatabaseHas('timezones', ['name' => 'Asia/Tokyo']);
    }

    public function test_can_get_user_settings_filtered_by_group(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.user.settings.index').'?group=appearance');

        $response->assertStatus(200)
            ->assertJsonStructure(['settings']);
    }

    public function test_can_get_global_settings_filtered_by_group(): void
    {
        $admin = $this->createWhitelistedAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson(route('api.settings.index').'?group=general');

        $response->assertStatus(200)
            ->assertJsonStructure(['settings']);
    }

    public function test_can_get_specific_global_setting(): void
    {
        $admin = $this->createWhitelistedAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson(route('api.settings.show', 'site_name'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'setting' => ['key', 'value', 'type', 'group', 'label', 'description', 'icon', 'referenceable'],
            ]);
    }

    public function test_can_get_specific_user_setting(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.user.settings.show', 'user_theme'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'setting' => ['key', 'value', 'type', 'group', 'label', 'description', 'icon', 'referenceable'],
            ]);
    }

    public function test_returns_404_for_non_existent_global_setting(): void
    {
        $admin = $this->createWhitelistedAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson(route('api.settings.show', 'non_existent_setting'));

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Setting not found.',
            ]);
    }

    public function test_returns_404_for_non_existent_user_setting(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.user.settings.show', 'non_existent_setting'));

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Setting not found.',
            ]);
    }

    public function test_can_create_new_global_setting(): void
    {
        $admin = $this->createWhitelistedAdmin();

        // First create a setting structure to update
        Setting::create([
            'key' => 'new_test_setting',
            'value' => json_encode('initial_value'),
            'type' => 'string',
            'group' => 'general',
            'scope' => 'global',
            'label' => 'New Test Setting',
            'is_public' => true,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson(route('api.settings.store'), [
                'key' => 'new_test_setting',
                'value' => 'test_value',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Setting saved successfully.',
            ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'new_test_setting',
            'scope' => 'global',
        ]);
    }

    public function test_can_update_global_setting(): void
    {
        $admin = $this->createWhitelistedAdmin();

        // First create a setting
        Setting::create([
            'key' => 'test_setting',
            'value' => json_encode('old_value'),
            'type' => 'string',
            'group' => 'general',
            'scope' => 'global',
            'label' => 'Test Setting',
            'is_public' => true,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->putJson(route('api.settings.update', 'test_setting'), [
                'value' => 'new_value',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Setting updated successfully.',
            ]);

        $setting = Setting::where('key', 'test_setting')
            ->where('scope', 'global')
            ->whereNull('settable_type')
            ->first();
        $this->assertEquals('new_value', $setting->getTypedValue());
    }

    public function test_can_delete_global_setting(): void
    {
        $admin = $this->createWhitelistedAdmin();

        // Create a setting
        Setting::create([
            'key' => 'deletable_setting',
            'value' => json_encode('test'),
            'type' => 'string',
            'group' => 'general',
            'scope' => 'global',
            'label' => 'Deletable Setting',
            'is_public' => true,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson(route('api.settings.destroy', 'deletable_setting'));

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Setting deleted successfully.',
            ]);

        $this->assertDatabaseMissing('settings', [
            'key' => 'deletable_setting',
            'scope' => 'global',
        ]);
    }

    public function test_returns_404_when_deleting_non_existent_setting(): void
    {
        $admin = $this->createWhitelistedAdmin();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson(route('api.settings.destroy', 'non_existent'));

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Setting not found.',
            ]);
    }

    public function test_can_get_countries_filtered_by_region(): void
    {
        $response = $this->getJson(route('api.countries.index').'?region=North America');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'countries' => [
                    '*' => ['id', 'code', 'name', 'flag_emoji'],
                ],
            ]);
    }

    public function test_can_get_specific_country_by_code(): void
    {
        $response = $this->getJson(route('api.countries.show', 'US'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'country' => ['id', 'code', 'name', 'flag_emoji'],
            ]);
    }

    public function test_returns_404_for_non_existent_country(): void
    {
        $response = $this->getJson(route('api.countries.show', 'XX'));

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Country not found.',
            ]);
    }

    public function test_can_get_timezones_filtered_by_region(): void
    {
        $response = $this->getJson(route('api.timezones.index').'?region=America');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'timezones' => [
                    '*' => ['id', 'name', 'display_name', 'offset_formatted'],
                ],
            ]);
    }

    public function test_can_get_specific_timezone(): void
    {
        $timezone = \App\Models\Timezone::first();

        $response = $this->getJson(route('api.timezones.show', $timezone->id));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'timezone' => ['id', 'name', 'display_name', 'offset_formatted'],
            ]);
    }

    public function test_returns_404_for_non_existent_timezone(): void
    {
        $response = $this->getJson(route('api.timezones.show', 99999));

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Timezone not found.',
            ]);
    }

    public function test_setting_value_is_properly_type_cast(): void
    {
        $admin = $this->createWhitelistedAdmin();
        $user = User::factory()->create();

        // Test boolean (using maintenance_mode global setting)
        $this->actingAs($admin, 'sanctum')
            ->putJson(route('api.settings.update', 'maintenance_mode'), [
                'value' => true,
            ]);

        $setting = Setting::where('key', 'maintenance_mode')
            ->whereNull('settable_id')
            ->first();
        $this->assertIsBool($setting->getTypedValue());

        // Test integer
        $this->actingAs($user, 'sanctum')
            ->putJson(route('api.user.settings.update-single', 'items_per_page'), [
                'value' => 25,
            ]);

        $setting = Setting::where('key', 'items_per_page')
            ->where('settable_id', $user->id)
            ->first();
        $this->assertIsInt($setting->getTypedValue());
    }

    public function test_user_cannot_access_settings_without_authentication(): void
    {
        $response = $this->getJson(route('api.user.settings.index'));

        $response->assertStatus(401);
    }

    public function test_setting_lists_are_ordered_correctly(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson(route('api.settings.lists', 'themes'));

        $response->assertStatus(200);

        $lists = $response->json('lists');
        $this->assertNotEmpty($lists);

        // Verify ordering
        for ($i = 1; $i < count($lists); $i++) {
            $this->assertLessThanOrEqual(
                $lists[$i]['order'],
                $lists[$i - 1]['order']
            );
        }
    }

    public function test_country_timezone_relationships_exist(): void
    {
        $this->assertDatabaseHas('country_timezone', [
            'country_id' => \App\Models\Country::where('code', 'US')->first()->id,
        ]);

        $this->assertDatabaseHas('country_timezone', [
            'timezone_id' => \App\Models\Timezone::where('name', 'America/New_York')->first()->id,
        ]);
    }

    public function test_user_settings_have_correct_scope(): void
    {
        $user = User::factory()->create();

        $userSetting = Setting::where('settable_id', $user->id)
            ->where('settable_type', User::class)
            ->first();

        $this->assertEquals('user', $userSetting->scope);
    }

    public function test_global_settings_have_null_settable(): void
    {
        $globalSetting = Setting::where('scope', 'global')->first();

        $this->assertNull($globalSetting->settable_type);
        $this->assertNull($globalSetting->settable_id);
    }
}
