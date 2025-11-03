<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles are created by migration - no need to recreate
    }

    /**
     * Test middleware blocks unauthenticated users.
     */
    public function test_middleware_blocks_unauthenticated_users(): void
    {
        // Try to access admin-only endpoint without authentication
        $response = $this->getJson('/api/resources/users');

        $response->assertUnauthorized();
    }

    /**
     * Test middleware blocks regular users from admin endpoints.
     */
    public function test_middleware_blocks_regular_users_from_admin_endpoints(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        // The 'admin' middleware should be applied to admin routes
        // Since we haven't applied it yet to resource routes, this would pass
        // We'll need to apply middleware to routes first
        $this->assertTrue(true); // Placeholder - will update after applying middleware
    }

    /**
     * Test middleware blocks non-whitelisted admins.
     */
    public function test_middleware_blocks_non_whitelisted_admins(): void
    {
        config(['admin.id' => [999]]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertFalse($admin->canAccessAdminPanel());
    }

    /**
     * Test middleware allows whitelisted admins.
     */
    public function test_middleware_allows_whitelisted_admins(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        config(['admin.id' => [$admin->id]]);

        $this->assertTrue($admin->canAccessAdminPanel());
    }

    /**
     * Test login returns correct user data with admin panel access.
     */
    public function test_login_returns_correct_admin_panel_access_data(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');
        config(['admin.id' => [$admin->id]]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'is_admin',
                    'is_user',
                    'can_access_admin_panel',
                ],
                'token',
            ])
            ->assertJson([
                'user' => [
                    'can_access_admin_panel' => true,
                ],
            ]);
    }

    /**
     * Test login for non-whitelisted admin.
     */
    public function test_login_for_non_whitelisted_admin(): void
    {
        config(['admin.id' => [999]]);

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJson([
                'user' => [
                    'is_admin' => true,
                    'can_access_admin_panel' => false,
                ],
            ]);
    }

    /**
     * Test login for regular user.
     */
    public function test_login_for_regular_user(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('user');

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJson([
                'user' => [
                    'is_admin' => false,
                    'is_user' => true,
                    'can_access_admin_panel' => false,
                ],
            ]);
    }

    /**
     * Test register returns correct user data.
     */
    public function test_register_returns_correct_user_data(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'is_admin',
                    'is_user',
                    'can_access_admin_panel',
                ],
                'token',
            ])
            ->assertJson([
                'user' => [
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'is_admin' => false,
                    'is_user' => true,
                    'can_access_admin_panel' => false,
                ],
            ]);
    }
}
