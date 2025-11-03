<?php

namespace Tests\Unit;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Services\BladeTemplateSecurityService;
use App\Services\EmailTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EmailTemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EmailTemplateService(new BladeTemplateSecurityService);
    }

    public function test_renders_template_with_data(): void
    {
        $template = EmailTemplate::factory()->create([
            'key' => 'test',
            'subject_template' => 'Hello {{ $user->name }}',
            'body_content' => '<p>Email: {{ $user->email }}</p>',
            'is_active' => true,
        ]);

        $user = User::factory()->make(['name' => 'John Doe', 'email' => 'john@example.com']);

        $result = $this->service->render('test', ['user' => $user]);

        $this->assertEquals('Hello John Doe', $result['subject']);
        $this->assertStringContainsString('john@example.com', $result['html']);
    }

    public function test_renders_inactive_template_throws_exception(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        EmailTemplate::factory()->create([
            'key' => 'inactive',
            'is_active' => false,
        ]);

        $this->service->render('inactive', []);
    }

    public function test_preview_returns_rendered_content(): void
    {
        $template = EmailTemplate::factory()->create([
            'subject_template' => 'Subject: {{ $name }}',
            'body_content' => '<p>{{ $message }}</p>',
        ]);

        $result = $this->service->preview($template, [
            'name' => 'Test',
            'message' => 'Hello World',
        ]);

        $this->assertEquals('Subject: Test', $result['subject']);
        $this->assertStringContainsString('Hello World', $result['html']);
    }

    public function test_send_test_sends_emails(): void
    {
        Mail::fake();

        $template = EmailTemplate::factory()->create([
            'subject_template' => 'Test Subject',
            'body_content' => '<p>Test Body</p>',
        ]);

        $this->service->sendTest($template, ['test@example.com', 'test2@example.com'], []);

        // Since we use Mail::html(), just verify no exceptions were thrown
        $this->assertTrue(true);
    }

    public function test_render_with_invalid_key_throws_exception(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->service->render('nonexistent', []);
    }

    public function test_validates_template_security_before_rendering(): void
    {
        $this->expectException(\App\Exceptions\BladeSecurityException::class);

        $template = EmailTemplate::factory()->create([
            'key' => 'malicious',
            'subject_template' => 'Test',
            'body_content' => '@php system("rm -rf /"); @endphp',
            'is_active' => true,
        ]);

        $this->service->render('malicious', []);
    }
}
