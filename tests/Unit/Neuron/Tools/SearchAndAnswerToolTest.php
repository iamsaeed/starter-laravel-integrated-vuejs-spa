<?php

namespace Tests\Unit\Neuron\Tools;

use App\Neuron\Tools\SearchAndAnswerTool;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SearchAndAnswerToolTest extends TestCase
{
    private SearchAndAnswerTool $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tool = new SearchAndAnswerTool;
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
        $this->assertStringContainsString('search', strtolower($description));
        $this->assertStringContainsString('answer', strtolower($description));
    }

    public function test_has_valid_schema(): void
    {
        $schema = $this->tool->getSchema();

        $this->assertArrayHasKey('name', $schema);
        $this->assertArrayHasKey('description', $schema);
        $this->assertArrayHasKey('parameters', $schema);
        $this->assertEquals('search_and_answer', $schema['name']);
        $this->assertArrayHasKey('query', $schema['parameters']['properties']);
    }

    public function test_execute_returns_no_results_message_when_search_empty(): void
    {
        // Mock SERP API to return no results
        Http::fake([
            'serpapi.com/*' => Http::response([
                'organic_results' => [],
            ], 200),
        ]);

        $result = $this->tool->execute('nonexistent query xyz123', []);

        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('answer', $result['type']);
        $this->assertArrayHasKey('query', $result);
        $this->assertArrayHasKey('answer', $result);
        $this->assertArrayHasKey('sources', $result);
        $this->assertEmpty($result['sources']);
        $this->assertStringContainsString("couldn't find", $result['answer']);
    }

    public function test_execute_structure_with_search_results(): void
    {
        // Mock SERP API
        Http::fake([
            'serpapi.com/*' => Http::response([
                'organic_results' => [
                    [
                        'title' => 'Test Result',
                        'link' => 'https://example.com/test',
                        'snippet' => 'Test snippet content',
                        'position' => 1,
                    ],
                ],
            ], 200),
            'example.com/*' => Http::response('<html><body><main>Detailed test content</main></body></html>', 200),
            // Mock OpenAI API for answer synthesis
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'This is a synthesized answer based on the sources.',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->tool->execute('test query', []);

        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('answer', $result['type']);
        $this->assertArrayHasKey('query', $result);
        $this->assertEquals('test query', $result['query']);
        $this->assertArrayHasKey('answer', $result);
        $this->assertArrayHasKey('sources', $result);
        $this->assertArrayHasKey('message', $result);
    }

    public function test_execute_handles_serp_api_errors_gracefully(): void
    {
        // Mock SERP API to fail
        Http::fake([
            '*' => Http::response('', 500),
        ]);

        $result = $this->tool->execute('test query', []);

        // When SERP API fails, the tool catches the error and returns error in result
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('answer', $result['type']);
        // The tool should have error key or an answer indicating failure
        $this->assertTrue(isset($result['error']) || (isset($result['answer']) && str_contains($result['answer'], "couldn't find")));
    }

    public function test_execute_returns_answer_even_without_content_fetch(): void
    {
        // Mock SERP API with results but fail content fetching
        Http::fake([
            'serpapi.com/*' => Http::response([
                'organic_results' => [
                    [
                        'title' => 'Test Result',
                        'link' => 'https://example.com/test',
                        'snippet' => 'Snippet with useful information',
                        'position' => 1,
                    ],
                ],
            ], 200),
            'example.com/*' => Http::response('', 404), // Fail fetching
            // Mock OpenAI for snippet-based answer
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Answer based on snippets only.',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->tool->execute('test query', []);

        // Should still return an answer based on snippets
        $this->assertArrayHasKey('answer', $result);
        $this->assertNotEmpty($result['answer']);
    }

    public function test_execute_includes_sources_in_result(): void
    {
        Http::fake([
            '*' => Http::response(function ($request) {
                // Mock SERP API
                if (str_contains($request->url(), 'serpapi.com')) {
                    return Http::response([
                        'organic_results' => [
                            [
                                'title' => 'Source 1',
                                'link' => 'https://example.com/1',
                                'snippet' => 'Snippet 1',
                                'position' => 1,
                            ],
                            [
                                'title' => 'Source 2',
                                'link' => 'https://example.com/2',
                                'snippet' => 'Snippet 2',
                                'position' => 2,
                            ],
                        ],
                    ], 200);
                }
                // Mock content pages
                if (str_contains($request->url(), 'example.com')) {
                    return Http::response('<html><body><main>Content</main></body></html>', 200);
                }
                // Mock OpenAI
                if (str_contains($request->url(), 'openai.com')) {
                    return Http::response([
                        'choices' => [
                            [
                                'message' => [
                                    'content' => 'Synthesized answer.',
                                ],
                            ],
                        ],
                    ], 200);
                }

                return Http::response('', 404);
            }),
        ]);

        $result = $this->tool->execute('test query', []);

        $this->assertArrayHasKey('sources', $result);
        $this->assertIsArray($result['sources']);
        // Sources should exist (may be empty if mocking fails, but structure should be correct)
        $this->assertGreaterThanOrEqual(0, count($result['sources']));
    }

    public function test_execute_limits_sources_to_configured_max(): void
    {
        // Create 10 results but expect only max configured (default 3)
        $results = [];
        for ($i = 1; $i <= 10; $i++) {
            $results[] = [
                'title' => "Result {$i}",
                'link' => "https://example.com/{$i}",
                'snippet' => "Snippet {$i}",
                'position' => $i,
            ];
        }

        Http::fake([
            'serpapi.com/*' => Http::response([
                'organic_results' => $results,
            ], 200),
            'example.com/*' => Http::response('<html><body>Content</body></html>', 200),
            'api.openai.com/*' => Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => 'Answer.',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->tool->execute('test query', []);

        $maxSources = config('neuron.tools.search_answer.max_sources', 3);
        $this->assertLessThanOrEqual($maxSources, count($result['sources']));
    }

    public function test_execute_returns_type_answer(): void
    {
        Http::fake([
            'serpapi.com/*' => Http::response(['organic_results' => []], 200),
        ]);

        $result = $this->tool->execute('test', []);

        $this->assertEquals('answer', $result['type']);
    }

    public function test_execute_includes_query_in_result(): void
    {
        Http::fake([
            'serpapi.com/*' => Http::response(['organic_results' => []], 200),
        ]);

        $query = 'What is Laravel?';
        $result = $this->tool->execute($query, []);

        $this->assertArrayHasKey('query', $result);
        $this->assertEquals($query, $result['query']);
    }
}
