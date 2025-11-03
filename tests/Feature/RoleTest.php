<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_are_seeded(): void
    {
        $this->assertDatabaseCount('roles', 2);

        $this->assertDatabaseHas('roles', [
            'slug' => 'admin',
            'name' => 'Admin',
        ]);

        $this->assertDatabaseHas('roles', [
            'slug' => 'user',
            'name' => 'User',
        ]);
    }

    public function test_user_can_be_assigned_a_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertNotNull($user->role());
        $this->assertEquals('user', $user->role()->slug);
        $this->assertDatabaseHas('role_user', [
            'user_id' => $user->id,
            'role_id' => Role::where('slug', 'user')->first()->id,
        ]);
    }

    public function test_user_can_be_assigned_admin_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertNotNull($user->role());
        $this->assertEquals('admin', $user->role()->slug);
        $this->assertTrue($user->isAdmin());
    }

    public function test_user_can_only_have_one_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertEquals('user', $user->role()->slug);

        // Assign admin role - should replace user role
        $user->assignRole('admin');

        $this->assertEquals('admin', $user->role()->slug);
        $this->assertCount(1, $user->roles);
    }

    public function test_assign_role_replaces_existing_role(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $userRoleId = Role::where('slug', 'user')->first()->id;
        $this->assertDatabaseHas('role_user', [
            'user_id' => $user->id,
            'role_id' => $userRoleId,
        ]);

        // Assign admin role
        $user->assignRole('admin');

        $adminRoleId = Role::where('slug', 'admin')->first()->id;

        // Should have admin role
        $this->assertDatabaseHas('role_user', [
            'user_id' => $user->id,
            'role_id' => $adminRoleId,
        ]);

        // Should not have user role anymore
        $this->assertDatabaseMissing('role_user', [
            'user_id' => $user->id,
            'role_id' => $userRoleId,
        ]);
    }

    public function test_has_role_method_works_correctly(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertTrue($user->hasRole('user'));
        $this->assertFalse($user->hasRole('admin'));
    }

    public function test_is_admin_method_works_correctly(): void
    {
        $adminUser = User::factory()->create();
        $adminUser->assignRole('admin');

        $regularUser = User::factory()->create();
        $regularUser->assignRole('user');

        $this->assertTrue($adminUser->isAdmin());
        $this->assertFalse($regularUser->isAdmin());
    }

    public function test_role_can_be_assigned_by_id(): void
    {
        $user = User::factory()->create();
        $roleId = Role::where('slug', 'admin')->first()->id;

        $user->assignRole($roleId);

        $this->assertEquals('admin', $user->role()->slug);
    }

    public function test_role_relationships_work(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $userRole = Role::where('slug', 'user')->first();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $user1 = User::factory()->create();
        $user1->assignRole('user');

        $user2 = User::factory()->create();
        $user2->assignRole('user');

        // Test role->users relationship
        // Refresh to get latest relationships
        $adminRole = $adminRole->fresh();
        $userRole = $userRole->fresh();

        $this->assertCount(1, $adminRole->users);
        $this->assertCount(2, $userRole->users);

        $this->assertTrue($adminRole->users->contains($admin));
        $this->assertTrue($userRole->users->contains($user1));
        $this->assertTrue($userRole->users->contains($user2));
    }

    public function test_new_user_registration_gets_default_user_role(): void
    {
        $response = $this->postJson(route('api.register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->role());
        $this->assertEquals('user', $user->role()->slug);
        $this->assertFalse($user->isAdmin());
    }

    public function test_role_has_name_and_description(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $userRole = Role::where('slug', 'user')->first();

        $this->assertEquals('Admin', $adminRole->name);
        $this->assertEquals('User', $userRole->name);
        $this->assertNotNull($adminRole->description);
        $this->assertNotNull($userRole->description);
    }
}
