<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles are created by migration - no need to recreate
    }

    /**
     * Test isUser method returns true for users with user role.
     */
    public function test_is_user_method_returns_true_for_user_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertTrue($user->isUser());
        $this->assertFalse($user->isAdmin());
    }

    /**
     * Test isUser method returns false for admins.
     */
    public function test_is_user_method_returns_false_for_admin_role(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertFalse($admin->isUser());
        $this->assertTrue($admin->isAdmin());
    }

    /**
     * Test canAccessAdminPanel returns false when user is not admin.
     */
    public function test_can_access_admin_panel_returns_false_for_non_admin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertFalse($user->canAccessAdminPanel());
    }

    /**
     * Test canAccessAdminPanel returns false when admin is not in whitelist.
     */
    public function test_can_access_admin_panel_returns_false_for_admin_not_in_whitelist(): void
    {
        // Set whitelist to a non-existent user ID
        config(['admin.id' => [999]]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertFalse($admin->canAccessAdminPanel());
        $this->assertTrue($admin->isAdmin());
    }

    /**
     * Test canAccessAdminPanel returns true when admin is in whitelist.
     */
    public function test_can_access_admin_panel_returns_true_for_whitelisted_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Add admin to whitelist
        config(['admin.id' => [$admin->id]]);

        $this->assertTrue($admin->canAccessAdminPanel());
    }

    /**
     * Test canAccessAdminPanel with multiple whitelisted admins.
     */
    public function test_can_access_admin_panel_with_multiple_whitelisted_admins(): void
    {
        $admin1 = User::factory()->create();
        $admin2 = User::factory()->create();
        $admin3 = User::factory()->create();

        $admin1->assignRole('admin');
        $admin2->assignRole('admin');
        $admin3->assignRole('admin');

        // Whitelist admin1 and admin3 only
        config(['admin.id' => [$admin1->id, $admin3->id]]);

        $this->assertTrue($admin1->canAccessAdminPanel());
        $this->assertFalse($admin2->canAccessAdminPanel());
        $this->assertTrue($admin3->canAccessAdminPanel());
    }

    /**
     * Test canAccessAdminPanel with empty whitelist.
     */
    public function test_can_access_admin_panel_with_empty_whitelist(): void
    {
        config(['admin.id' => []]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertFalse($admin->canAccessAdminPanel());
    }

    /**
     * Test UserResource exposes can_access_admin_panel field.
     */
    public function test_user_resource_exposes_can_access_admin_panel_field(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        config(['admin.id' => [$admin->id]]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/me');

        $response->assertOk()
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'is_admin',
                    'is_user',
                    'can_access_admin_panel',
                ],
            ])
            ->assertJson([
                'user' => [
                    'can_access_admin_panel' => true,
                ],
            ]);
    }

    /**
     * Test UserResource shows false for non-whitelisted admin.
     */
    public function test_user_resource_shows_false_for_non_whitelisted_admin(): void
    {
        config(['admin.id' => [999]]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/me');

        $response->assertOk()
            ->assertJson([
                'user' => [
                    'is_admin' => true,
                    'can_access_admin_panel' => false,
                ],
            ]);
    }

    /**
     * Test UserResource shows correct values for regular user.
     */
    public function test_user_resource_shows_correct_values_for_regular_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/me');

        $response->assertOk()
            ->assertJson([
                'user' => [
                    'is_admin' => false,
                    'is_user' => true,
                    'can_access_admin_panel' => false,
                ],
            ]);
    }
}
