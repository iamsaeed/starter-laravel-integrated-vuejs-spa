<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        // Add admin to whitelist
        config(['admin.id' => [$this->admin->id]]);

        $this->user = User::factory()->create();
        $this->user->assignRole('user');
    }

    public function test_admin_can_list_email_templates(): void
    {
        EmailTemplate::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/email-templates');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'key', 'name', 'subject_template', 'is_active'],
                ],
                'meta' => ['total', 'per_page', 'current_page'],
            ]);
    }

    public function test_non_admin_cannot_access_email_templates(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/email-templates');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_email_template(): void
    {
        $data = [
            'key' => 'test_template',
            'name' => 'Test Template',
            'subject_template' => 'Test Subject - {{ $user->name }}',
            'body_content' => '<!DOCTYPE html><html><body>Hello {{ $user->name }}</body></html>',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/email-templates', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['key' => 'test_template']);

        $this->assertDatabaseHas('email_templates', [
            'key' => 'test_template',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_update_email_template(): void
    {
        $template = EmailTemplate::factory()->create();

        $data = [
            'key' => $template->key,
            'name' => 'Updated Name',
            'subject_template' => $template->subject_template,
            'body_content' => $template->body_content,
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/email-templates/{$template->id}", $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'name' => 'Updated Name',
            'updated_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_delete_email_template(): void
    {
        $template = EmailTemplate::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/email-templates/{$template->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('email_templates', ['id' => $template->id]);
    }

    public function test_admin_can_duplicate_email_template(): void
    {
        $template = EmailTemplate::factory()->create(['key' => 'original']);

        $initialCount = EmailTemplate::count();

        $response = $this->actingAs($this->admin)
            ->postJson("/api/email-templates/{$template->id}/duplicate");

        $response->assertStatus(201)
            ->assertJsonPath('data.name', $template->name.' (Copy)');

        $this->assertDatabaseCount('email_templates', $initialCount + 1);
    }

    public function test_admin_can_preview_email_template(): void
    {
        $template = EmailTemplate::factory()->create([
            'key' => 'user_welcome',
            'subject_template' => 'Welcome {{ $user->name }}',
            'body_content' => '<!DOCTYPE html><html><body>Hello {{ $user->name }}</body></html>',
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/email-templates/{$template->id}/preview");

        $response->assertStatus(200)
            ->assertJsonStructure(['subject', 'html']);
    }

    public function test_admin_can_send_test_email(): void
    {
        Mail::fake();

        $template = EmailTemplate::factory()->create(['key' => 'user_welcome']);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/email-templates/{$template->id}/send-test", [
                'emails' => ['test@example.com'],
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Test emails sent successfully']);
    }

    public function test_admin_can_get_available_variables(): void
    {
        $template = EmailTemplate::factory()->create(['key' => 'user_welcome']);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/email-templates/{$template->id}/variables");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_cannot_create_template_with_blocked_directives(): void
    {
        $data = [
            'key' => 'malicious',
            'name' => 'Malicious Template',
            'subject_template' => 'Test',
            'body_content' => '@php system("rm -rf /"); @endphp',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/email-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body_content']);
    }

    public function test_cannot_create_template_with_dangerous_patterns(): void
    {
        // Test that dangerous file operations are blocked
        $data = [
            'key' => 'dangerous',
            'name' => 'Dangerous Template',
            'subject_template' => 'Test',
            'body_content' => '{{ file_put_contents("/tmp/test.txt", "data") }}',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/email-templates', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body_content']);
    }

    public function test_templates_can_be_filtered_by_active_status(): void
    {
        // Clear seeded templates for this test
        EmailTemplate::query()->delete();

        EmailTemplate::factory()->create(['is_active' => true]);
        EmailTemplate::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/email-templates?filter_active=1');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertTrue($data[0]['is_active']);
    }

    public function test_templates_can_be_searched_by_name(): void
    {
        // Clear seeded templates for this test
        EmailTemplate::query()->delete();

        EmailTemplate::factory()->create(['name' => 'Password Reset Email']);
        EmailTemplate::factory()->create(['name' => 'Welcome Email']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/email-templates?search=Password');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('Password', $data[0]['name']);
    }

    public function test_store_upserts_template_by_key(): void
    {
        $original = EmailTemplate::factory()->create([
            'key' => 'duplicate_key',
            'name' => 'Original Template',
        ]);

        $data = [
            'key' => 'duplicate_key',
            'name' => 'Updated Template',
            'subject_template' => 'Updated Subject',
            'body_content' => '<!DOCTYPE html><html><body>Updated content</body></html>',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/email-templates', $data);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Template']);

        // Verify only one template exists with this key
        $this->assertEquals(1, EmailTemplate::where('key', 'duplicate_key')->count());

        // Verify it's the same ID (updated, not created)
        $this->assertDatabaseHas('email_templates', [
            'id' => $original->id,
            'key' => 'duplicate_key',
            'name' => 'Updated Template',
            'updated_by' => $this->admin->id,
        ]);
    }

    public function test_forgot_password_template_exists_and_renders_correctly(): void
    {
        // Seed the forgot_password template
        $this->seed(\Database\Seeders\EmailTemplatesSeeder::class);

        $template = EmailTemplate::where('key', 'forgot_password')->first();

        $this->assertNotNull($template);
        $this->assertEquals('Forgot Password Email', $template->name);
        $this->assertTrue($template->is_active);

        // Test rendering with sample data
        $testUser = (object) ['name' => 'John Doe'];
        $resetUrl = 'https://example.com/reset-password?token=abc123';
        $expiresIn = '60 minutes';

        $data = [
            'user' => $testUser,
            'reset_url' => $resetUrl,
            'expires_in' => $expiresIn,
        ];

        $subject = \Illuminate\Support\Facades\Blade::render($template->subject_template, $data);
        $html = \Illuminate\Support\Facades\Blade::render($template->body_content, $data);

        // Assert subject renders correctly
        $this->assertStringContainsString('Reset Your Password', $subject);
        $this->assertStringContainsString(config('app.name'), $subject);

        // Assert body renders correctly
        $this->assertStringContainsString('John Doe', $html);
        $this->assertStringContainsString($resetUrl, $html);
        $this->assertStringContainsString($expiresIn, $html);
        $this->assertStringContainsString('Reset Password', $html);

        // Assert HTML structure
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
        $this->assertStringContainsString('</html>', $html);
    }
}
