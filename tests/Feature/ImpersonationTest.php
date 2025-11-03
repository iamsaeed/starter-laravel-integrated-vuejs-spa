<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\ImpersonationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $regularUser;

    protected ImpersonationService $impersonationService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create or get admin role
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'slug' => 'admin',
                'description' => 'Administrator role',
            ]
        );

        // Create admin user
        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $this->admin->roles()->attach($adminRole);

        // Add admin to the whitelist so canAccessAdminPanel() returns true
        config(['admin.id' => [$this->admin->id]]);

        // Create regular user
        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
        ]);

        $this->impersonationService = app(ImpersonationService::class);
    }

    public function test_admin_can_impersonate_regular_user(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/impersonation/{$this->regularUser->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Successfully impersonating user.',
                'user' => [
                    'id' => $this->regularUser->id,
                    'name' => $this->regularUser->name,
                    'email' => $this->regularUser->email,
                ],
                'impersonation' => [
                    'is_impersonating' => true,
                ],
            ]);
    }

    public function test_regular_user_cannot_impersonate(): void
    {
        $anotherUser = User::factory()->create([
            'email' => 'another@example.com',
        ]);

        $response = $this->actingAs($this->regularUser)
            ->postJson("/api/impersonation/{$anotherUser->id}");

        $response->assertForbidden()
            ->assertJson([
                'message' => 'Forbidden',
            ]);
    }

    public function test_cannot_impersonate_other_admins(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $anotherAdmin = User::factory()->create([
            'name' => 'Another Admin',
            'email' => 'another-admin@example.com',
        ]);
        $anotherAdmin->roles()->attach($adminRole);

        // Add the other admin to whitelist so middleware lets the request through
        config(['admin.id' => [$this->admin->id, $anotherAdmin->id]]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/impersonation/{$anotherAdmin->id}");

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Cannot impersonate other administrators.',
            ]);
    }

    public function test_can_stop_impersonating(): void
    {
        // Manually set up impersonation in session
        Session::put('impersonating', Crypt::encryptString(json_encode([
            'admin_id' => $this->admin->id,
            'started_at' => now()->toDateTimeString(),
        ])));

        // Stop impersonation (acting as the regular user now)
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->postJson('/api/impersonation/stop');

        $response->assertOk()
            ->assertJson([
                'message' => 'Successfully stopped impersonating.',
            ]);
    }

    public function test_cannot_stop_impersonating_when_not_impersonating(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/impersonation/stop');

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Not currently impersonating.',
            ]);
    }

    public function test_can_get_impersonation_status(): void
    {
        // When not impersonating
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/impersonation/status');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'is_impersonating' => false,
                    'admin' => null,
                    'started_at' => null,
                ],
            ]);

        // Manually set up impersonation in session
        Session::put('impersonating', Crypt::encryptString(json_encode([
            'admin_id' => $this->admin->id,
            'started_at' => now()->toDateTimeString(),
        ])));

        // Check status while impersonating
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/impersonation/status');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'is_impersonating' => true,
                    'admin' => [
                        'id' => $this->admin->id,
                        'name' => $this->admin->name,
                        'email' => $this->admin->email,
                    ],
                ],
            ]);
    }

    public function test_impersonation_service_stores_data_encrypted(): void
    {
        $this->impersonationService->impersonate($this->admin, $this->regularUser);

        // Get the raw session data
        $encryptedData = Session::get('impersonating');

        // Verify it's encrypted (should be a string)
        $this->assertIsString($encryptedData);

        // Verify we can decrypt it
        $decrypted = Crypt::decryptString($encryptedData);
        $data = json_decode($decrypted, true);

        $this->assertIsArray($data);
        $this->assertEquals($this->admin->id, $data['admin_id']);
        $this->assertArrayHasKey('started_at', $data);
    }

    public function test_impersonation_service_returns_correct_status(): void
    {
        // Not impersonating
        $status = $this->impersonationService->getStatus();
        $this->assertFalse($status['is_impersonating']);

        // Start impersonating
        $this->impersonationService->impersonate($this->admin, $this->regularUser);

        $status = $this->impersonationService->getStatus();
        $this->assertTrue($status['is_impersonating']);
        $this->assertEquals($this->admin->id, $status['admin']['id']);
        $this->assertEquals($this->admin->name, $status['admin']['name']);
    }

    public function test_impersonation_service_throws_exception_for_non_admin(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only administrators can impersonate users.');

        $this->impersonationService->impersonate($this->regularUser, $this->admin);
    }

    public function test_impersonation_service_throws_exception_for_other_admins(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $anotherAdmin = User::factory()->create([
            'email' => 'another-admin@example.com',
        ]);
        $anotherAdmin->roles()->attach($adminRole);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot impersonate other administrators.');

        $this->impersonationService->impersonate($this->admin, $anotherAdmin);
    }

    public function test_impersonation_data_persists_across_sessions(): void
    {
        $this->impersonationService->impersonate($this->admin, $this->regularUser);

        // Verify data is retrievable
        $data = $this->impersonationService->getImpersonationData();
        $this->assertIsArray($data);
        $this->assertEquals($this->admin->id, $data['admin_id']);

        // Verify admin can be retrieved
        $admin = $this->impersonationService->getImpersonatingAdmin();
        $this->assertNotNull($admin);
        $this->assertEquals($this->admin->id, $admin->id);
    }

    public function test_cannot_impersonate_non_existent_user(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/impersonation/99999');

        $response->assertNotFound();
    }

    public function test_requires_authentication_to_impersonate(): void
    {
        $response = $this->postJson("/api/impersonation/{$this->regularUser->id}");

        $response->assertUnauthorized();
    }

    public function test_requires_authentication_to_stop_impersonating(): void
    {
        $response = $this->postJson('/api/impersonation/stop');

        $response->assertUnauthorized();
    }

    public function test_requires_authentication_to_get_status(): void
    {
        $response = $this->getJson('/api/impersonation/status');

        $response->assertUnauthorized();
    }
}
