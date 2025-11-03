<?php

namespace App\Neuron\Tools;

use Illuminate\Support\Facades\Log;
use NeuronAI\Agent;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\OpenAI\OpenAI;

/**
 * Search and Answer Tool
 *
 * Performs internet searches and synthesizes human-readable answers using AI.
 * Combines SearchEngineTool, WebContentFetcherTool, and AI synthesis.
 */
class SearchAndAnswerTool implements Tool
{
    protected SearchEngineTool $searchTool;

    protected WebContentFetcherTool $contentFetcher;

    protected int $maxSourcesToFetch;

    protected string $aiProvider;

    protected string $aiModel;

    public function __construct()
    {
        $this->searchTool = new SearchEngineTool;
        $this->contentFetcher = new WebContentFetcherTool;
        $this->maxSourcesToFetch = config('neuron.tools.search_answer.max_sources', 3);
        $this->aiProvider = config('neuron.ai.provider', 'openai');
        $this->aiModel = config('neuron.ai.model', 'gpt-4o-mini');
    }

    /**
     * Execute search and synthesize answer
     */
    public function execute(string $message, array $context): array
    {
        try {
            $query = $message;

            // Step 1: Perform search using SearchEngineTool
            Log::info('SearchAndAnswerTool: Starting search', ['query' => $query]);
            $searchResults = $this->searchTool->execute($query, $context);

            if (empty($searchResults['results'])) {
                return [
                    'type' => 'answer',
                    'query' => $query,
                    'answer' => "I couldn't find any information about '{$query}'. Please try rephrasing your question.",
                    'sources' => [],
                    'message' => "I couldn't find any information about '{$query}'.",
                ];
            }

            // Step 2: Fetch content from top results
            Log::info('SearchAndAnswerTool: Fetching content from top results');
            $contents = $this->fetchTopResults($searchResults['results']);

            if (empty($contents)) {
                // Fallback to search snippets only
                Log::warning('SearchAndAnswerTool: No content fetched, using snippets only');
                $answer = $this->synthesizeFromSnippets($query, $searchResults['results']);

                return [
                    'type' => 'answer',
                    'query' => $query,
                    'answer' => $answer,
                    'sources' => array_slice($searchResults['results'], 0, 5),
                    'message' => $answer,
                ];
            }

            // Step 3: Synthesize answer using AI
            Log::info('SearchAndAnswerTool: Synthesizing answer with AI');
            $answer = $this->synthesizeAnswer($query, $contents, $searchResults['results']);

            return [
                'type' => 'answer',
                'query' => $query,
                'answer' => $answer,
                'sources' => array_slice($searchResults['results'], 0, $this->maxSourcesToFetch),
                'source_count' => count($contents),
                'message' => $answer,
            ];
        } catch (\Exception $e) {
            Log::error('SearchAndAnswerTool error', [
                'message' => $message,
                'error' => $e->getMessage(),
            ]);

            return [
                'type' => 'answer',
                'query' => $message,
                'error' => 'Failed to generate answer: '.$e->getMessage(),
                'answer' => '',
                'sources' => [],
                'message' => 'Sorry, I encountered an error while generating the answer: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Fetch content from top search results
     */
    protected function fetchTopResults(array $results): array
    {
        $contents = [];
        $fetchedCount = 0;

        foreach ($results as $result) {
            if ($fetchedCount >= $this->maxSourcesToFetch) {
                break;
            }

            try {
                $contentResult = $this->contentFetcher->execute('', ['url' => $result['link']]);

                if (! empty($contentResult['content']) && ! isset($contentResult['error'])) {
                    $contents[] = [
                        'url' => $result['link'],
                        'title' => $result['title'],
                        'content' => $contentResult['content'],
                    ];
                    $fetchedCount++;
                }
            } catch (\Exception $e) {
                Log::warning('SearchAndAnswerTool: Failed to fetch content', [
                    'url' => $result['link'],
                    'error' => $e->getMessage(),
                ]);

                // Continue to next result
                continue;
            }
        }

        return $contents;
    }

    /**
     * Synthesize answer from fetched contents using AI
     */
    protected function synthesizeAnswer(string $query, array $contents, array $searchResults): string
    {
        // Build context from fetched contents
        $contextParts = [];
        foreach ($contents as $i => $content) {
            $num = $i + 1;
            $contextParts[] = "Source {$num}: {$content['title']}\nURL: {$content['url']}\nContent: {$content['content']}\n";
        }

        $context = implode("\n---\n\n", $contextParts);

        // Create AI prompt
        $prompt = <<<PROMPT
You are a helpful assistant that answers questions based on provided sources.

Question: {$query}

I have searched the internet and found the following information:

{$context}

Please provide a comprehensive, accurate, and well-structured answer to the question based on the sources above.

Guidelines:
1. Synthesize information from multiple sources when relevant
2. Be concise but thorough
3. Use natural, conversational language
4. If sources contradict each other, mention both perspectives
5. If sources don't fully answer the question, acknowledge what is covered and what isn't
6. Do not make up information not present in the sources
7. You may reference sources by their title or number (e.g., "According to Source 1...")

Answer:
PROMPT;

        // Create agent and get response
        $provider = $this->getAIProvider();

        $agent = Agent::make()
            ->withProvider($provider)
            ->withInstructions('You are a helpful assistant that answers questions based on provided sources.');

        $userMessage = UserMessage::make($prompt);
        $response = $agent->chat($userMessage);

        return trim($response->getContent());
    }

    /**
     * Fallback: Synthesize answer from search snippets only
     */
    protected function synthesizeFromSnippets(string $query, array $results): string
    {
        // Build context from snippets
        $snippets = [];
        foreach (array_slice($results, 0, 5) as $i => $result) {
            if (! empty($result['snippet'])) {
                $num = $i + 1;
                $snippets[] = "{$num}. {$result['title']}\n   {$result['snippet']}\n   Source: {$result['link']}";
            }
        }

        $context = implode("\n\n", $snippets);

        $prompt = <<<PROMPT
You are a helpful assistant that answers questions based on search result snippets.

Question: {$query}

Here are the top search result snippets:

{$context}

Please provide a helpful answer based on these snippets. Be concise and acknowledge that this is based on limited information from search results.

Answer:
PROMPT;

        $provider = $this->getAIProvider();

        $agent = Agent::make()
            ->withProvider($provider)
            ->withInstructions('You are a helpful assistant that answers questions based on search result snippets.');

        $userMessage = UserMessage::make($prompt);
        $response = $agent->chat($userMessage);

        return trim($response->getContent());
    }

    /**
     * Get configured AI provider
     */
    protected function getAIProvider(): OpenAI
    {
        // For now, always use OpenAI
        // In the future, this can be extended to support other providers
        return new OpenAI(
            config('neuron.providers.openai.api_key'),
            $this->aiModel
        );
    }

    /**
     * Get the tool description
     */
    public function getDescription(): string
    {
        return 'Search the internet and provide AI-synthesized answers with source citations';
    }

    /**
     * Get the tool schema for AI agents
     */
    public function getSchema(): array
    {
        return [
            'name' => 'search_and_answer',
            'description' => 'Search the internet for information and synthesize a comprehensive answer to the user\'s question',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'query' => [
                        'type' => 'string',
                        'description' => 'The question or search query to find information about and answer',
                    ],
                ],
                'required' => ['query'],
            ],
        ];
    }
}
