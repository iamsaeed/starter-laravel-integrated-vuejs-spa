<?php

namespace Tests\Unit;

use App\Exceptions\BladeSecurityException;
use App\Services\BladeTemplateSecurityService;
use Tests\TestCase;

class BladeTemplateSecurityServiceTest extends TestCase
{
    protected BladeTemplateSecurityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BladeTemplateSecurityService;
    }

    public function test_allows_safe_blade_syntax(): void
    {
        $template = 'Hello {{ $user->name }}, your email is {{ $user->email }}';

        $this->service->validate($template);

        $this->assertTrue(true);
    }

    public function test_blocks_php_directive(): void
    {
        $this->expectException(BladeSecurityException::class);

        $template = '@php system("rm -rf /"); @endphp';

        $this->service->validate($template);
    }

    public function test_blocks_include_directive(): void
    {
        $this->expectException(BladeSecurityException::class);

        $template = '@include("some.file")';

        $this->service->validate($template);
    }

    public function test_blocks_component_directive(): void
    {
        $this->expectException(BladeSecurityException::class);

        $template = '@component("alert") Test @endcomponent';

        $this->service->validate($template);
    }

    public function test_blocks_php_opening_tags(): void
    {
        $this->expectException(BladeSecurityException::class);

        $template = '<?php echo "test"; ?>';

        $this->service->validate($template);
    }

    public function test_blocks_system_calls(): void
    {
        $this->expectException(BladeSecurityException::class);

        $template = '{{ system("ls") }}';

        $this->service->validate($template);
    }

    public function test_blocks_exec_calls(): void
    {
        $this->expectException(BladeSecurityException::class);

        $template = '{{ exec("whoami") }}';

        $this->service->validate($template);
    }

    public function test_blocks_eval_calls(): void
    {
        $this->expectException(BladeSecurityException::class);

        $template = '{{ eval("dangerous code") }}';

        $this->service->validate($template);
    }

    public function test_blocks_file_operations(): void
    {
        $this->expectException(BladeSecurityException::class);

        $template = '{{ file_get_contents("/etc/passwd") }}';

        $this->service->validate($template);
    }

    public function test_validates_blade_syntax(): void
    {
        // Blade is lenient and doesn't throw errors for most syntax issues
        // This test just verifies that the validation runs without throwing unexpected errors
        $template = 'Valid content {{ $user->name }}';

        $this->service->validate($template);

        $this->assertTrue(true);
    }

    public function test_allows_common_blade_directives(): void
    {
        $template = <<<'BLADE'
        @if($user->isAdmin())
            <p>Welcome Admin</p>
        @else
            <p>Welcome User</p>
        @endif

        @foreach($items as $item)
            {{ $item->name }}
        @endforeach
        BLADE;

        $this->service->validate($template);

        $this->assertTrue(true);
    }

    public function test_validate_and_get_errors_returns_errors_for_blocked_directives(): void
    {
        $template = '@php dangerous code @endphp';

        $errors = $this->service->validateAndGetErrors($template);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('php', $errors[0]['message']);
    }

    public function test_validate_and_get_errors_returns_empty_for_safe_content(): void
    {
        $template = 'Hello {{ $user->name }}';

        $errors = $this->service->validateAndGetErrors($template);

        $this->assertEmpty($errors);
    }
}
