<?php

namespace App\Neuron\Tools;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Internet Search Engine Tool using SERP API
 *
 * Performs web searches using SerpAPI to find information from the internet.
 * Supports Google, Bing, Yahoo, and other search engines.
 */
class SearchEngineTool implements Tool
{
    protected ?string $apiKey;

    protected string $engine;

    protected int $maxResults;

    public function __construct()
    {
        $this->apiKey = config('services.serp.api_key', env('VITE_SERP_API_KEY')) ?? '';
        $this->engine = config('services.serp.engine', env('VITE_SERP_ENGINE', 'google'));
        $this->maxResults = config('neuron.tools.search.max_results', 10);
    }

    /**
     * Execute the internet search
     */
    public function execute(string $message, array $context): array
    {
        try {
            // Extract search query from message
            $query = $this->extractQuery($message);

            if (empty($query)) {
                return [
                    'type' => 'search',
                    'query' => '',
                    'results' => [],
                    'message' => 'Please provide a search query.',
                ];
            }

            // Perform the search
            $results = $this->performSearch($query);

            return [
                'type' => 'search',
                'query' => $query,
                'engine' => $this->engine,
                'results' => $results,
                'count' => count($results),
                'message' => $this->formatResponse($query, $results),
            ];
        } catch (\Exception $e) {
            Log::error('SearchEngineTool error', [
                'message' => $message,
                'error' => $e->getMessage(),
            ]);

            return [
                'type' => 'search',
                'query' => $message,
                'error' => 'Failed to perform search: '.$e->getMessage(),
                'results' => [],
                'message' => 'Sorry, I encountered an error while searching: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Extract the search query from the user message
     */
    protected function extractQuery(string $message): string
    {
        // Remove common search prefixes
        $patterns = [
            '/^search\s+for\s+/i',
            '/^search\s+/i',
            '/^find\s+information\s+about\s+/i',
            '/^find\s+/i',
            '/^look\s+for\s+/i',
            '/^google\s+/i',
            '/^what\s+is\s+/i',
            '/^who\s+is\s+/i',
            '/^where\s+is\s+/i',
            '/^when\s+/i',
            '/^how\s+to\s+/i',
        ];

        $query = trim($message);

        foreach ($patterns as $pattern) {
            $query = preg_replace($pattern, '', $query);
        }

        // Remove quotes if the entire query is quoted
        if (preg_match('/^["\'](.+)["\']$/', $query, $matches)) {
            $query = $matches[1];
        }

        return trim($query);
    }

    /**
     * Perform the actual search using SERP API
     */
    protected function performSearch(string $query): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('SERP API key not configured. Please set VITE_SERP_API_KEY in your .env file.');
        }

        $params = [
            'api_key' => $this->apiKey,
            'q' => $query,
            'num' => $this->maxResults,
            'engine' => $this->engine,
        ];

        // Make request to SERP API
        $response = Http::timeout(30)
            ->get('https://serpapi.com/search', $params);

        if (! $response->successful()) {
            throw new \RuntimeException('SERP API request failed: '.$response->status());
        }

        $data = $response->json();

        // Parse results based on engine
        return $this->parseResults($data);
    }

    /**
     * Parse SERP API results into a consistent format
     */
    protected function parseResults(array $data): array
    {
        $results = [];

        // Google organic results
        if (isset($data['organic_results']) && is_array($data['organic_results'])) {
            foreach ($data['organic_results'] as $result) {
                $results[] = [
                    'title' => $result['title'] ?? '',
                    'link' => $result['link'] ?? '',
                    'snippet' => $result['snippet'] ?? '',
                    'displayed_link' => $result['displayed_link'] ?? $result['link'] ?? '',
                    'position' => $result['position'] ?? 0,
                ];

                if (count($results) >= $this->maxResults) {
                    break;
                }
            }
        }

        // Answer box (featured snippet)
        if (isset($data['answer_box'])) {
            array_unshift($results, [
                'title' => $data['answer_box']['title'] ?? 'Answer',
                'link' => $data['answer_box']['link'] ?? '',
                'snippet' => $data['answer_box']['answer'] ?? $data['answer_box']['snippet'] ?? '',
                'displayed_link' => $data['answer_box']['displayed_link'] ?? '',
                'position' => 0,
                'featured' => true,
            ]);
        }

        // Knowledge graph
        if (isset($data['knowledge_graph']) && empty($results)) {
            $kg = $data['knowledge_graph'];
            $results[] = [
                'title' => $kg['title'] ?? 'Knowledge Graph',
                'link' => $kg['website'] ?? '',
                'snippet' => $kg['description'] ?? '',
                'displayed_link' => $kg['website'] ?? '',
                'position' => 0,
                'featured' => true,
            ];
        }

        return $results;
    }

    /**
     * Format the search response message
     */
    protected function formatResponse(string $query, array $results): string
    {
        if (empty($results)) {
            return "No results found for '{$query}'.";
        }

        $count = count($results);
        $message = "Found {$count} result".($count !== 1 ? 's' : '')." for '{$query}':\n\n";

        foreach (array_slice($results, 0, 5) as $i => $result) {
            $num = $i + 1;
            $featured = isset($result['featured']) ? ' ⭐' : '';
            $message .= "{$num}. {$result['title']}{$featured}\n";
            $message .= "   {$result['link']}\n";
            if (! empty($result['snippet'])) {
                $snippet = $this->truncateSnippet($result['snippet'], 150);
                $message .= "   {$snippet}\n";
            }
            $message .= "\n";
        }

        if ($count > 5) {
            $remaining = $count - 5;
            $message .= "...and {$remaining} more result".($remaining !== 1 ? 's' : '').'.';
        }

        return trim($message);
    }

    /**
     * Truncate snippet to specified length
     */
    protected function truncateSnippet(string $text, int $length = 150): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $truncated = mb_substr($text, 0, $length);
        $lastSpace = mb_strrpos($truncated, ' ');

        if ($lastSpace !== false) {
            $truncated = mb_substr($truncated, 0, $lastSpace);
        }

        return $truncated.'...';
    }

    /**
     * Get the tool description
     */
    public function getDescription(): string
    {
        return 'Search the internet for information using '.$this->engine;
    }

    /**
     * Get the tool schema for AI agents
     */
    public function getSchema(): array
    {
        return [
            'name' => 'search_internet',
            'description' => 'Search the internet for current information, news, documentation, or any web content',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'The search query to look up on the internet',
                    ],
                ],
                'required' => ['query'],
            ],
        ];
    }
}
