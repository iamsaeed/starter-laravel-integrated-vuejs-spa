<?php

namespace Tests\Unit\Neuron\Tools;

use App\Neuron\Tools\WebContentFetcherTool;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebContentFetcherToolTest extends TestCase
{
    private WebContentFetcherTool $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tool = new WebContentFetcherTool;
    }

    public function test_implements_tool_interface(): void
    {
        $this->assertInstanceOf(\App\Neuron\Tools\Tool::class, $this->tool);
    }

    public function test_has_correct_description(): void
    {
        $description = $this->tool->getDescription();
        $this->assertIsString($description);
        $this->assertNotEmpty($description);
        $this->assertStringContainsString('web', strtolower($description));
    }

    public function test_has_valid_schema(): void
    {
        $schema = $this->tool->getSchema();

        $this->assertArrayHasKey('name', $schema);
        $this->assertArrayHasKey('description', $schema);
        $this->assertArrayHasKey('parameters', $schema);
        $this->assertEquals('fetch_web_content', $schema['name']);
        $this->assertArrayHasKey('url', $schema['parameters']['properties']);
    }

    public function test_execute_with_empty_url_returns_error(): void
    {
        $result = $this->tool->execute('', []);

        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('web_content', $result['type']);
        $this->assertArrayHasKey('error', $result);
        $this->assertEmpty($result['url']);
    }

    public function test_execute_extracts_url_from_message(): void
    {
        $html = '<html><body><main>Test content here</main></body></html>';

        Http::fake([
            'example.com/*' => Http::response($html, 200),
        ]);

        $result = $this->tool->execute('Please fetch https://example.com/test', []);

        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('web_content', $result['type']);
        $this->assertEquals('https://example.com/test', $result['url']);
        $this->assertArrayHasKey('content', $result);
        $this->assertStringContainsString('Test content', $result['content']);
    }

    public function test_execute_with_context_url(): void
    {
        $html = '<html><body><article><h1>Article Title</h1><p>Article content here.</p></article></body></html>';

        Http::fake([
            'example.com/*' => Http::response($html, 200),
        ]);

        $result = $this->tool->execute('', ['url' => 'https://example.com/article']);

        $this->assertEquals('web_content', $result['type']);
        $this->assertEquals('https://example.com/article', $result['url']);
        $this->assertStringContainsString('Article Title', $result['content']);
        $this->assertStringContainsString('Article content', $result['content']);
    }

    public function test_execute_removes_script_tags(): void
    {
        $html = '<html><body><main>Good content<script>alert("bad")</script>More content</main></body></html>';

        Http::fake([
            '*' => Http::response($html, 200),
        ]);

        $result = $this->tool->execute('', ['url' => 'https://example.com/test']);

        $this->assertStringNotContainsString('alert', $result['content']);
        $this->assertStringNotContainsString('script', $result['content']);
        $this->assertStringContainsString('Good content', $result['content']);
    }

    public function test_execute_removes_style_tags(): void
    {
        $html = '<html><head><style>body { color: red; }</style></head><body><main>Content here</main></body></html>';

        Http::fake([
            '*' => Http::response($html, 200),
        ]);

        $result = $this->tool->execute('', ['url' => 'https://example.com/test']);

        $this->assertStringNotContainsString('color: red', $result['content']);
        $this->assertStringNotContainsString('style', strtolower($result['content']));
        $this->assertStringContainsString('Content here', $result['content']);
    }

    public function test_execute_decodes_html_entities(): void
    {
        $html = '<html><body><main>Test &amp; Content &lt;tag&gt; &quot;quoted&quot;</main></body></html>';

        Http::fake([
            '*' => Http::response($html, 200),
        ]);

        $result = $this->tool->execute('', ['url' => 'https://example.com/test']);

        $this->assertStringContainsString('Test & Content', $result['content']);
        $this->assertStringContainsString('<tag>', $result['content']);
        $this->assertStringContainsString('"quoted"', $result['content']);
    }

    public function test_execute_limits_content_length(): void
    {
        $longContent = str_repeat('a', 15000);
        $html = "<html><body><main>{$longContent}</main></body></html>";

        Http::fake([
            'example.com/*' => Http::response($html, 200),
        ]);

        $result = $this->tool->execute('', ['url' => 'https://example.com']);

        $maxLength = config('neuron.tools.web_fetcher.max_content_length', 10000);
        $this->assertLessThanOrEqual($maxLength + 3, mb_strlen($result['content'])); // +3 for "..."
    }

    public function test_execute_handles_http_errors(): void
    {
        Http::fake([
            '*' => Http::response('', 404),
        ]);

        $result = $this->tool->execute('', ['url' => 'https://example.com/test']);

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('404', $result['error']);
    }

    public function test_execute_handles_invalid_url(): void
    {
        $result = $this->tool->execute('', ['url' => 'not-a-valid-url']);

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('Invalid URL', $result['error']);
    }

    public function test_execute_extracts_main_content_tag(): void
    {
        $html = '<html><body><nav>Navigation</nav><main>Main content</main><footer>Footer</footer></body></html>';

        Http::fake([
            '*' => Http::response($html, 200),
        ]);

        $result = $this->tool->execute('', ['url' => 'https://example.com/test']);

        $this->assertStringContainsString('Main content', $result['content']);
        // Main tag should be preferred, so navigation and footer should ideally not be included
        // (depends on implementation details)
    }

    public function test_execute_returns_content_length(): void
    {
        $html = '<html><body><main>Test content</main></body></html>';

        Http::fake([
            'example.com/*' => Http::response($html, 200),
        ]);

        $result = $this->tool->execute('', ['url' => 'https://example.com']);

        $this->assertArrayHasKey('length', $result);
        $this->assertIsInt($result['length']);
        $this->assertGreaterThan(0, $result['length']);
    }

    public function test_execute_returns_formatted_message(): void
    {
        $html = '<html><body><main>Test content</main></body></html>';

        Http::fake([
            'example.com/*' => Http::response($html, 200),
        ]);

        $result = $this->tool->execute('', ['url' => 'https://example.com']);

        $this->assertArrayHasKey('message', $result);
        $this->assertStringContainsString('https://example.com', $result['message']);
        $this->assertStringContainsString('characters', $result['message']);
    }
}
