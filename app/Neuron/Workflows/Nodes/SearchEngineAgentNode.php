<?php

namespace App\Neuron\Workflows\Nodes;

use App\Neuron\Events\ChatEvent;
use App\Neuron\Tools\SearchAndAnswerTool;
use Illuminate\Support\Facades\Log;
use NeuronAI\Workflow\Event;
use NeuronAI\Workflow\Node;

/**
 * Search Engine Agent Node
 *
 * Dedicated node for handling internet search queries with AI-synthesized answers.
 * This node provides comprehensive search capabilities with source attribution.
 */
class SearchEngineAgentNode extends Node
{
    private SearchAndAnswerTool $searchTool;

    private float $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->searchTool = new SearchAndAnswerTool;
    }

    /**
     * Handle the search event
     */
    public function handle(Event $event): Event
    {
        $userMessage = $event->get('message');
        $context = $event->get('context', []);

        try {
            Log::info('SearchEngineAgentNode: Processing search request', [
                'message' => $userMessage,
            ]);

            // Execute search and answer tool
            $result = $this->searchTool->execute($userMessage, $context);

            Log::info('SearchEngineAgentNode: Search completed', [
                'has_answer' => ! empty($result['answer']),
                'sources_count' => count($result['sources'] ?? []),
            ]);

            return new ChatEvent([
                'original_message' => $userMessage,
                'tools_used' => ['search_and_answer'],
                'raw_results' => ['search' => $result],
                'formatted_result' => $result,
                'context' => $context,
                'metadata' => [
                    'execution_time' => $this->getExecutionTime(),
                    'node' => 'SearchEngineAgentNode',
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('SearchEngineAgentNode: Error processing search', [
                'message' => $userMessage,
                'error' => $e->getMessage(),
            ]);

            return new ChatEvent([
                'original_message' => $userMessage,
                'tools_used' => ['search_and_answer'],
                'formatted_result' => [
                    'type' => 'error',
                    'query' => $userMessage,
                    'error' => 'Search failed: '.$e->getMessage(),
                    'message' => 'Sorry, I encountered an error while searching. Please try again.',
                ],
                'context' => $context,
                'metadata' => [
                    'execution_time' => $this->getExecutionTime(),
                    'error' => $e->getMessage(),
                    'node' => 'SearchEngineAgentNode',
                ],
            ]);
        }
    }

    /**
     * Get execution time in seconds
     */
    private function getExecutionTime(): float
    {
        return round(microtime(true) - $this->startTime, 3);
    }
}
