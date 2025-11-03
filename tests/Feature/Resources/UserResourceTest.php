<?php

namespace Tests\Feature\Resources;

use App\Enums\Status;
use App\Models\Role;
use App\Models\User;

class UserResourceTest extends ResourceTestCase
{
    protected string $resourceKey = 'users';

    public function test_can_list_resources(): void
    {
        User::factory()->count(5)->create();

        $response = $this->getJson($this->resourcePath, $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'status'],
                ],
                'current_page',
                'last_page',
                'per_page',
                'total',
            ])
            ->assertJsonPath('total', 6); // 5 + 1 auth user
    }

    public function test_can_create_resource(): void
    {
        $role = Role::factory()->create();

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'status' => Status::Active->value,
            'role_id' => $role->id,
        ];

        $response = $this->postJson($this->resourcePath, $data, $this->authHeaders());

        $response->assertStatus(201)
            ->assertJsonPath('message', 'User created successfully')
            ->assertJsonPath('data.name', 'John Doe')
            ->assertJsonPath('data.email', 'john@example.com');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_can_show_resource(): void
    {
        $user = User::factory()->create(['name' => 'Jane Doe']);

        $response = $this->getJson("{$this->resourcePath}/{$user->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', 'Jane Doe');
    }

    public function test_can_update_resource(): void
    {
        $user = User::factory()->create();

        $data = [
            'name' => 'Updated Name',
            'email' => $user->email,
            'status' => Status::Inactive->value,
        ];

        $response = $this->putJson("{$this->resourcePath}/{$user->id}", $data, $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'User updated successfully')
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.status', Status::Inactive->value);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_resource(): void
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("{$this->resourcePath}/{$user->id}", [], $this->authHeaders());

        $response->assertOk()
            ->assertJsonPath('message', 'User deleted successfully');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_validation_fails_on_create(): void
    {
        $response = $this->postJson($this->resourcePath, [], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'status']);
    }

    public function test_validation_fails_for_duplicate_email(): void
    {
        $existingUser = User::factory()->create();

        $data = [
            'name' => 'New User',
            'email' => $existingUser->email,
            'password' => 'password123',
            'status' => Status::Active->value,
        ];

        $response = $this->postJson($this->resourcePath, $data, $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_can_search_resources(): void
    {
        User::factory()->create(['name' => 'Alice Smith', 'email' => 'alice@example.com']);
        User::factory()->create(['name' => 'Bob Jones', 'email' => 'bob@example.com']);

        $response = $this->getJson("{$this->resourcePath}?search=Alice", $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Alice Smith');
    }

    public function test_can_search_by_email(): void
    {
        User::factory()->create(['name' => 'Test User', 'email' => 'unique@example.com']);
        User::factory()->create(['name' => 'Another User', 'email' => 'another@example.com']);

        $response = $this->getJson("{$this->resourcePath}?search=unique", $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'unique@example.com');
    }

    public function test_can_sort_resources(): void
    {
        User::factory()->create(['name' => 'Zebra']);
        User::factory()->create(['name' => 'Alpha']);

        $response = $this->getJson("{$this->resourcePath}?sort=name&direction=asc", $this->authHeaders());

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals('Alpha', $data[0]['name']);
    }

    public function test_can_sort_descending(): void
    {
        User::factory()->create(['name' => 'Alpha', 'email' => 'alpha@example.com']);
        User::factory()->create(['name' => 'Zebra', 'email' => 'zebra@example.com']);

        $response = $this->getJson("{$this->resourcePath}?sort=name&direction=desc", $this->authHeaders());

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals('Zebra', $data[0]['name']);
    }

    public function test_can_filter_by_status(): void
    {
        User::factory()->create(['status' => Status::Active]);
        User::factory()->create(['status' => Status::Inactive]);

        $response = $this->getJson(
            "{$this->resourcePath}?filters[status]=".Status::Active->value,
            $this->authHeaders()
        );

        $response->assertOk();
        $data = $response->json('data');
        foreach ($data as $user) {
            $this->assertEquals(Status::Active->value, $user['status']);
        }
    }

    public function test_can_bulk_delete_resources(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $response = $this->postJson(
            "{$this->resourcePath}/bulk/delete",
            ['ids' => [$user1->id, $user2->id]],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonPath('affected', 2);

        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseMissing('users', ['id' => $user2->id]);
        $this->assertDatabaseHas('users', ['id' => $user3->id]);
    }

    public function test_can_bulk_update_resources(): void
    {
        $user1 = User::factory()->create(['status' => Status::Active]);
        $user2 = User::factory()->create(['status' => Status::Active]);

        $response = $this->postJson(
            "{$this->resourcePath}/bulk/update",
            [
                'ids' => [$user1->id, $user2->id],
                'data' => ['status' => Status::Inactive->value],
            ],
            $this->authHeaders()
        );

        $response->assertOk()
            ->assertJsonPath('affected', 2);

        $this->assertDatabaseHas('users', ['id' => $user1->id, 'status' => Status::Inactive->value]);
        $this->assertDatabaseHas('users', ['id' => $user2->id, 'status' => Status::Inactive->value]);
    }

    public function test_pagination_works_correctly(): void
    {
        User::factory()->count(25)->create();

        $response = $this->getJson("{$this->resourcePath}?perPage=10", $this->authHeaders());

        $response->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('per_page', 10)
            ->assertJsonPath('last_page', 3); // 26 users (25 + 1 auth) / 10 = 3 pages
    }

    public function test_resource_not_found_returns_404(): void
    {
        $response = $this->getJson("{$this->resourcePath}/99999", $this->authHeaders());

        $response->assertNotFound();
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson($this->resourcePath);

        $response->assertUnauthorized();
    }
}
