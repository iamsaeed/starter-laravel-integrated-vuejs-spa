<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProtectedResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles are created by migration - no need to recreate
    }

    /**
     * Test unauthenticated users cannot access resources.
     */
    public function test_unauthenticated_users_cannot_access_resources(): void
    {
        $response = $this->getJson('/api/resources/users');

        $response->assertUnauthorized();
    }

    /**
     * Test regular users cannot access resources.
     */
    public function test_regular_users_cannot_access_resources(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/resources/users');

        $response->assertForbidden()
            ->assertJson(['message' => 'Forbidden']);
    }

    /**
     * Test non-whitelisted admins cannot access resources.
     */
    public function test_non_whitelisted_admins_cannot_access_resources(): void
    {
        config(['admin.id' => [999]]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/resources/users');

        $response->assertForbidden()
            ->assertJson(['message' => 'Forbidden']);
    }

    /**
     * Test whitelisted admins can access resources.
     */
    public function test_whitelisted_admins_can_access_resources(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        config(['admin.id' => [$admin->id]]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/resources/users/meta');

        $response->assertOk();
    }

    /**
     * Test regular users cannot access global settings.
     */
    public function test_regular_users_cannot_access_global_settings(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/settings');

        $response->assertForbidden();
    }

    /**
     * Test non-whitelisted admins cannot access global settings.
     */
    public function test_non_whitelisted_admins_cannot_access_global_settings(): void
    {
        config(['admin.id' => [999]]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/settings');

        $response->assertForbidden();
    }

    /**
     * Test whitelisted admins can access global settings.
     */
    public function test_whitelisted_admins_can_access_global_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        config(['admin.id' => [$admin->id]]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/settings');

        $response->assertOk();
    }

    /**
     * Test regular users cannot access email templates.
     */
    public function test_regular_users_cannot_access_email_templates(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/email-templates');

        $response->assertForbidden();
    }

    /**
     * Test whitelisted admins can access email templates.
     */
    public function test_whitelisted_admins_can_access_email_templates(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        config(['admin.id' => [$admin->id]]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/email-templates');

        $response->assertOk();
    }

    /**
     * Test all users can access user settings.
     */
    public function test_all_users_can_access_user_settings(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/user/settings');

        $response->assertOk();
    }

    /**
     * Test all authenticated users can access settings lists.
     */
    public function test_all_authenticated_users_can_access_settings_lists(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/settings/lists/countries');

        $response->assertOk();
    }

    /**
     * Test all authenticated users can access profile endpoints.
     */
    public function test_all_authenticated_users_can_access_profile_endpoints(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/me');

        $response->assertOk();
    }
}
