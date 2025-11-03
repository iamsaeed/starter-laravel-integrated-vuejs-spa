<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_modify_other_users_settings(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User1 creates a setting
        $this->actingAs($user1, 'sanctum')
            ->putJson(route('api.user.settings.update-single', 'user_theme'), [
                'value' => 'ocean',
            ]);

        // User2 tries to modify User1's setting (should create their own)
        $this->actingAs($user2, 'sanctum')
            ->putJson(route('api.user.settings.update-single', 'user_theme'), [
                'value' => 'sunset',
            ]);

        // Verify both users have their own settings
        $user1Setting = Setting::where('key', 'user_theme')
            ->where('settable_id', $user1->id)
            ->first();
        $this->assertEquals('ocean', $user1Setting->getTypedValue());

        $user2Setting = Setting::where('key', 'user_theme')
            ->where('settable_id', $user2->id)
            ->first();
        $this->assertEquals('sunset', $user2Setting->getTypedValue());
    }

    public function test_unauthenticated_user_cannot_access_global_settings(): void
    {
        $response = $this->getJson(route('api.settings.index'));

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_modify_settings(): void
    {
        $response = $this->postJson(route('api.settings.store'), [
            'key' => 'test_setting',
            'value' => 'test_value',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_settings(): void
    {
        $response = $this->deleteJson(route('api.settings.destroy', 'site_name'));

        $response->assertStatus(401);
    }

    public function test_user_cannot_access_private_settings(): void
    {
        $user = User::factory()->create();

        // Create a private global setting
        Setting::create([
            'key' => 'private_api_key',
            'value' => json_encode('secret_key_12345'),
            'type' => 'string',
            'group' => 'general',
            'scope' => 'global',
            'label' => 'Private API Key',
            'is_public' => false,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson(route('api.settings.update', 'private_api_key'), [
                'value' => 'hacked_key',
            ]);

        // Should be forbidden due to is_public = false
        $response->assertStatus(403);
    }

    public function test_setting_key_cannot_be_too_long(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        config(['admin.id' => [$user->id]]);

        $longKey = str_repeat('a', 256); // Exceeds 255 character limit

        $response = $this->actingAs($user, 'sanctum')
            ->postJson(route('api.settings.store'), [
                'key' => $longKey,
                'value' => 'test',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['key']);
    }

    public function test_bulk_update_validates_settings_array(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson(route('api.user.settings.update'), [
                // Missing 'settings' key
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['settings']);
    }

    public function test_user_settings_are_isolated_per_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Get user1's settings
        $response1 = $this->actingAs($user1, 'sanctum')
            ->getJson(route('api.user.settings.index'));

        $response1->assertStatus(200);
        $user1Settings = $response1->json('settings');

        // Get user2's settings
        $response2 = $this->actingAs($user2, 'sanctum')
            ->getJson(route('api.user.settings.index'));

        $response2->assertStatus(200);
        $user2Settings = $response2->json('settings');

        // Both should have settings (from observer)
        $this->assertNotEmpty($user1Settings);
        $this->assertNotEmpty($user2Settings);

        // Modify user1's theme
        $this->actingAs($user1, 'sanctum')
            ->putJson(route('api.user.settings.update-single', 'user_theme'), [
                'value' => 'crimson',
            ]);

        // User2's settings should be unchanged
        $user2ThemeSetting = Setting::where('key', 'user_theme')
            ->where('settable_id', $user2->id)
            ->first();

        // User2 should still have default theme (from observer)
        $this->assertNotEquals('crimson', $user2ThemeSetting->getTypedValue());
    }

    public function test_deleted_user_settings_do_not_affect_other_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Both users have their default settings from observer
        $this->assertDatabaseHas('settings', [
            'key' => 'user_theme',
            'settable_id' => $user1->id,
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'user_theme',
            'settable_id' => $user2->id,
        ]);

        // Delete user1's settings
        Setting::where('settable_id', $user1->id)->delete();

        // User2's settings should still exist
        $this->assertDatabaseHas('settings', [
            'key' => 'user_theme',
            'settable_id' => $user2->id,
        ]);
    }
}
