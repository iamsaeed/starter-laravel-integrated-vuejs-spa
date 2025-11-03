<?php

namespace Tests\Feature\Api;

use App\Enums\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourcePatchTest extends TestCase
{
    use RefreshDatabase;

    protected User $authUser;

    protected string $resourcePath = '/api/resources/users';

    protected function setUp(): void
    {
        parent::setUp();

        // Find or create admin role and assign to auth user
        $adminRole = \App\Models\Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin', 'description' => 'Administrator role']
        );

        $this->authUser = User::factory()->create();
        $this->authUser->roles()->attach($adminRole);
        
        // Add user to admin whitelist
        config(['admin.id' => [$this->authUser->id]]);
    }

    protected function authHeaders(): array
    {
        $token = $this->authUser->createToken('test')->plainTextToken;

        return [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];
    }

    // ========================================
    // Single Field Update Tests
    // ========================================

    public function test_can_patch_single_field_status(): void
    {
        $user = User::factory()->create(['status' => Status::Active]);

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['status' => Status::Inactive->value],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonPath('message', 'User updated successfully')
            ->assertJsonPath('data.status', Status::Inactive->value);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => Status::Inactive->value,
        ]);
    }

    public function test_can_patch_single_field_name(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['name' => 'Updated Name'],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_patch_single_field_email(): void
    {
        $user = User::factory()->create(['email' => 'original@example.com']);

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['email' => 'updated@example.com'],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonPath('data.email', 'updated@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'updated@example.com',
        ]);
    }

    // ========================================
    // Multiple Fields Update Tests
    // ========================================

    public function test_can_patch_multiple_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'status' => Status::Active,
        ]);

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            [
                'name' => 'Updated Name',
                'status' => Status::Inactive->value,
            ],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.status', Status::Inactive->value);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'status' => Status::Inactive->value,
        ]);
    }

    public function test_can_patch_name_and_email(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
            ],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonPath('data.name', 'Jane Smith')
            ->assertJsonPath('data.email', 'jane@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);
    }

    // ========================================
    // Validation Tests
    // ========================================

    public function test_patch_validates_email_format(): void
    {
        $user = User::factory()->create();

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['email' => 'invalid-email'],
            $this->authHeaders()
        );

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['email']);
    }

    public function test_patch_validates_unique_email(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['email' => 'existing@example.com'],
            $this->authHeaders()
        );

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed')
            ->assertJsonValidationErrors(['email']);
    }

    public function test_patch_allows_same_email_for_same_user(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['email' => 'user@example.com'],
            $this->authHeaders()
        );

        $response->assertOk();
    }

    public function test_patch_validates_status_enum(): void
    {
        $user = User::factory()->create();

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['status' => 'invalid-status'],
            $this->authHeaders()
        );

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Validation failed');
    }

    // ========================================
    // Required Fields Tests (Should NOT be required in PATCH)
    // ========================================

    public function test_patch_does_not_require_name_field(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['status' => Status::Inactive->value], // Only updating status, not name
            $this->authHeaders()
        );

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Original Name', // Name should remain unchanged
            'status' => Status::Inactive->value,
        ]);
    }

    public function test_patch_does_not_require_email_field(): void
    {
        $user = User::factory()->create(['email' => 'original@example.com']);

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['status' => Status::Inactive->value], // Only updating status, not email
            $this->authHeaders()
        );

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'original@example.com', // Email should remain unchanged
            'status' => Status::Inactive->value,
        ]);
    }

    public function test_patch_does_not_require_all_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'status' => Status::Active,
        ]);

        // Update only name, leaving email and other fields unchanged
        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['name' => 'Jane Doe'],
            $this->authHeaders()
        );

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Jane Doe',
            'email' => 'john@example.com', // Unchanged
            'status' => Status::Active->value, // Unchanged
        ]);
    }

    // ========================================
    // Edge Cases
    // ========================================

    public function test_patch_with_no_fields_returns_error(): void
    {
        $user = User::factory()->create();

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            [], // Empty data
            $this->authHeaders()
        );

        $response->assertStatus(422)
            ->assertJsonPath('message', 'No fields to update');
    }

    public function test_patch_non_existent_resource_returns_404(): void
    {
        $response = $this->patchJson(
            "{$this->resourcePath}/99999",
            ['status' => Status::Inactive->value],
            $this->authHeaders()
        );

        $response->assertStatus(404);
    }

    public function test_patch_requires_authentication(): void
    {
        $user = User::factory()->create();

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['status' => Status::Inactive->value]
        );

        $response->assertStatus(401);
    }

    public function test_patch_ignores_unknown_fields(): void
    {
        $user = User::factory()->create(['name' => 'Original Name']);

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            [
                'name' => 'Updated Name',
                'unknown_field' => 'some value', // Should be ignored
            ],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');

        // Make sure unknown field wasn't added to database
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    // ========================================
    // Toggle Use Case Tests
    // ========================================

    public function test_patch_supports_toggle_status_from_active_to_inactive(): void
    {
        $user = User::factory()->create(['status' => Status::Active]);

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['status' => Status::Inactive->value],
            $this->authHeaders()
        );

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => Status::Inactive->value,
        ]);
    }

    public function test_patch_supports_toggle_status_from_inactive_to_active(): void
    {
        $user = User::factory()->create(['status' => Status::Inactive]);

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['status' => Status::Active->value],
            $this->authHeaders()
        );

        $response->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => Status::Active->value,
        ]);
    }

    public function test_patch_multiple_toggles_in_sequence(): void
    {
        $user = User::factory()->create(['status' => Status::Active]);

        // Toggle to inactive
        $response1 = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['status' => Status::Inactive->value],
            $this->authHeaders()
        );
        $response1->assertOk();

        // Toggle back to active
        $response2 = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['status' => Status::Active->value],
            $this->authHeaders()
        );
        $response2->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => Status::Active->value,
        ]);
    }

    // ========================================
    // Password Field Tests
    // ========================================

    public function test_patch_can_update_password(): void
    {
        $user = User::factory()->create();

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['password' => 'newpassword123'],
            $this->authHeaders()
        );

        $response->assertOk();

        // Verify password was hashed
        $user->refresh();
        $this->assertNotEquals('newpassword123', $user->password);
        $this->assertTrue(\Hash::check('newpassword123', $user->password));
    }

    public function test_patch_validates_password_min_length(): void
    {
        $user = User::factory()->create();

        $response = $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['password' => 'short'],
            $this->authHeaders()
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // ========================================
    // Data Integrity Tests
    // ========================================

    public function test_patch_does_not_affect_other_users(): void
    {
        $user1 = User::factory()->create(['name' => 'User 1']);
        $user2 = User::factory()->create(['name' => 'User 2']);

        $this->patchJson(
            "{$this->resourcePath}/{$user1->id}",
            ['name' => 'Updated User 1'],
            $this->authHeaders()
        );

        $this->assertDatabaseHas('users', [
            'id' => $user2->id,
            'name' => 'User 2', // Should remain unchanged
        ]);
    }

    public function test_patch_preserves_created_at_timestamp(): void
    {
        $user = User::factory()->create();
        $originalCreatedAt = $user->created_at;

        $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['name' => 'Updated Name'],
            $this->authHeaders()
        );

        $user->refresh();
        $this->assertEquals($originalCreatedAt->timestamp, $user->created_at->timestamp);
    }

    public function test_patch_updates_updated_at_timestamp(): void
    {
        $user = User::factory()->create();
        $originalUpdatedAt = $user->updated_at;

        sleep(1); // Ensure time difference

        $this->patchJson(
            "{$this->resourcePath}/{$user->id}",
            ['name' => 'Updated Name'],
            $this->authHeaders()
        );

        $user->refresh();
        $this->assertTrue($user->updated_at->isAfter($originalUpdatedAt));
    }
}
