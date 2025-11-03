<?php

namespace App\Neuron\Tools;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Web Content Fetcher Tool
 *
 * Fetches and extracts readable content from web pages.
 * Cleans HTML and returns main text content.
 */
class WebContentFetcherTool implements Tool
{
    protected int $timeout;

    protected int $maxContentLength;

    public function __construct()
    {
        $this->timeout = config('neuron.tools.web_fetcher.timeout', 30);
        $this->maxContentLength = config('neuron.tools.web_fetcher.max_content_length', 10000);
    }

    /**
     * Fetch and extract content from a URL
     */
    public function execute(string $message, array $context): array
    {
        try {
            // Extract URL from message or context
            $url = $context['url'] ?? $this->extractUrl($message);

            if (empty($url)) {
                return [
                    'type' => 'web_content',
                    'url' => '',
                    'content' => '',
                    'error' => 'No URL provided',
                    'message' => 'Please provide a URL to fetch content from.',
                ];
            }

            // Fetch the content
            $content = $this->fetchContent($url);

            return [
                'type' => 'web_content',
                'url' => $url,
                'content' => $content,
                'length' => mb_strlen($content),
                'message' => $this->formatResponse($url, $content),
            ];
        } catch (\Exception $e) {
            Log::error('WebContentFetcherTool error', [
                'message' => $message,
                'context' => $context,
                'error' => $e->getMessage(),
            ]);

            return [
                'type' => 'web_content',
                'url' => $context['url'] ?? $message,
                'error' => 'Failed to fetch content: '.$e->getMessage(),
                'content' => '',
                'message' => 'Sorry, I encountered an error while fetching the content: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Extract URL from message
     */
    protected function extractUrl(string $message): string
    {
        // Match URLs in the message
        if (preg_match('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $message, $matches)) {
            return $matches[0];
        }

        return '';
    }

    /**
     * Fetch and clean content from URL
     */
    protected function fetchContent(string $url): string
    {
        // Validate URL
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('Invalid URL provided');
        }

        // Fetch the page
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; NeuronAI/1.0; +https://neuron-ai.dev)',
            ])
            ->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException('Failed to fetch URL: HTTP '.$response->status());
        }

        $html = $response->body();

        // Extract and clean content
        $content = $this->extractMainContent($html);

        // Limit content length
        if (mb_strlen($content) > $this->maxContentLength) {
            $content = mb_substr($content, 0, $this->maxContentLength).'...';
        }

        return $content;
    }

    /**
     * Extract main content from HTML
     */
    protected function extractMainContent(string $html): string
    {
        // Remove script and style tags
        $html = preg_replace('#<script[^>]*>.*?</script>#is', '', $html);
        $html = preg_replace('#<style[^>]*>.*?</style>#is', '', $html);

        // Remove HTML comments
        $html = preg_replace('#<!--.*?-->#s', '', $html);

        // Try to find main content areas
        $contentPatterns = [
            '#<main[^>]*>(.*?)</main>#is',
            '#<article[^>]*>(.*?)</article>#is',
            '#<div[^>]*class="[^"]*content[^"]*"[^>]*>(.*?)</div>#is',
            '#<div[^>]*id="[^"]*content[^"]*"[^>]*>(.*?)</div>#is',
            '#<body[^>]*>(.*?)</body>#is',
        ];

        $mainContent = '';
        foreach ($contentPatterns as $pattern) {
            if (preg_match($pattern, $html, $matches)) {
                $mainContent = $matches[1];
                break;
            }
        }

        // Fallback to full HTML if no main content found
        if (empty($mainContent)) {
            $mainContent = $html;
        }

        // Remove all HTML tags
        $text = strip_tags($mainContent);

        // Decode HTML entities
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\n\s*\n/', "\n\n", $text);

        return trim($text);
    }

    /**
     * Format the response message
     */
    protected function formatResponse(string $url, string $content): string
    {
        $length = mb_strlen($content);
        $preview = mb_substr($content, 0, 200);

        if ($length > 200) {
            $preview .= '...';
        }

        return "Fetched content from {$url} ({$length} characters):\n\n{$preview}";
    }

    /**
     * Get the tool description
     */
    public function getDescription(): string
    {
        return 'Fetch and extract readable content from web pages';
    }

    /**
     * Get the tool schema for AI agents
     */
    public function getSchema(): array
    {
        return [
            'name' => 'fetch_web_content',
            'description' => 'Fetch and extract the main text content from a web page URL',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'url' => [
                        'type' => 'string',
                        'description' => 'The URL of the web page to fetch content from',
                    ],
                ],
                'required' => ['url'],
            ],
        ];
    }
}
