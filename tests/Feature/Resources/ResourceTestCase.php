<?php

namespace Tests\Feature\Resources;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class ResourceTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected string $resourceKey;

    protected string $resourcePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('admin');

        // Add user to admin whitelist
        config(['admin.id' => [$this->user->id]]);

        $this->resourcePath = "/api/resources/{$this->resourceKey}";
    }

    /**
     * Get authenticated headers.
     */
    protected function authHeaders(): array
    {
        $token = $this->user->createToken('test')->plainTextToken;

        return [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];
    }

    /**
     * Test getting resource metadata.
     */
    public function test_can_get_resource_metadata(): void
    {
        $response = $this->getJson("{$this->resourcePath}/meta", $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'key',
                'label',
                'singularLabel',
                'title',
                'searchable',
                'search',
                'perPage',
                'fields' => [
                    '*' => [
                        'type',
                        'attribute',
                        'label',
                        'sortable',
                        'searchable',
                        'required',
                        'nullable',
                        'showOnIndex',
                        'showOnDetail',
                        'showOnForm',
                        'default',
                        'meta',
                    ],
                ],
                'filters',
                'actions',
            ]);
    }

    /**
     * Test listing resources with pagination.
     */
    abstract public function test_can_list_resources(): void;

    /**
     * Test creating a resource.
     */
    abstract public function test_can_create_resource(): void;

    /**
     * Test showing a single resource.
     */
    abstract public function test_can_show_resource(): void;

    /**
     * Test updating a resource.
     */
    abstract public function test_can_update_resource(): void;

    /**
     * Test deleting a resource.
     */
    abstract public function test_can_delete_resource(): void;

    /**
     * Test validation fails when required fields are missing.
     */
    abstract public function test_validation_fails_on_create(): void;

    /**
     * Test search functionality.
     */
    abstract public function test_can_search_resources(): void;

    /**
     * Test sorting functionality.
     */
    abstract public function test_can_sort_resources(): void;

    /**
     * Test bulk delete.
     */
    abstract public function test_can_bulk_delete_resources(): void;
}
