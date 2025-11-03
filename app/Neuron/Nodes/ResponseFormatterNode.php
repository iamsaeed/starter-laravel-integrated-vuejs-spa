<?php

namespace App\Neuron\Nodes;

use Illuminate\Support\Facades\Log;
use NeuronAI\Agent;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Workflow\Event;
use NeuronAI\Workflow\Node;

/**
 * Formats the structured response into natural language
 */
class ResponseFormatterNode extends Node
{
    private ?Agent $formatterAgent = null;

    /**
     * Handle the event and format the response
     */
    public function handle(Event $event): Event
    {
        $jsonData = $event->all();

        try {
            // Initialize formatting agent if configured to use AI
            if (config('neuron.workflows.chat.ai_provider')) {
                $this->initializeFormatterAgent();
            }

            // Convert JSON to natural language
            $naturalResponse = $this->formatToNaturalLanguage($jsonData);

            return new Event([
                'response' => $naturalResponse,
                'structured_data' => $jsonData,
                'type' => 'chat_response',
                'tools_used' => $jsonData['tools_used'] ?? [],
                'metadata' => $jsonData['metadata'] ?? [],
            ]);

        } catch (\Exception $e) {
            Log::error('Error in ResponseFormatterNode', [
                'error' => $e->getMessage(),
                'data' => $jsonData,
            ]);

            // Fallback to simple formatting
            return $this->createFallbackResponse($jsonData);
        }
    }

    /**
     * Initialize the formatting agent
     */
    private function initializeFormatterAgent(): void
    {
        if (! $this->formatterAgent) {
            $provider = new OpenAI(
                config('neuron.providers.openai.api_key'),
                config('neuron.providers.openai.model', 'gpt-4o-mini')
            );

            $this->formatterAgent = Agent::make()
                ->withProvider($provider)
                ->withInstructions($this->getFormatterPrompt());
        }
    }

    /**
     * Format the data to natural language
     */
    private function formatToNaturalLanguage(array $data): string
    {
        // Handle error responses
        if (isset($data['formatted_result']['error'])) {
            return $this->formatError($data['formatted_result']);
        }

        // Handle different response types
        $result = $data['formatted_result'] ?? [];

        switch ($result['type'] ?? '') {
            case 'expense':
                return $this->formatExpenseResponse($result);

            case 'database':
                return $this->formatDatabaseResponse($result);

            case 'search':
                return $this->formatSearchResponse($result);

            case 'conversation':
                return $result['response'] ?? 'I understand your request. How can I help you further?';

            case 'combined':
                return $this->formatCombinedResponse($result);

            default:
                // Use AI formatting if available
                if ($this->formatterAgent) {
                    return $this->formatWithAI($data);
                }

                // Fallback to simple response
                return $this->formatSimpleResponse($result);
        }
    }

    /**
     * Format expense-related responses
     */
    private function formatExpenseResponse(array $result): string
    {
        $action = $result['action'] ?? '';

        switch ($action) {
            case 'added':
                $expense = $result['expense'] ?? [];

                return sprintf(
                    "✅ I've added an expense of $%.2f for %s in the %s category.",
                    $expense['amount'] ?? 0,
                    $expense['description'] ?? 'your purchase',
                    $expense['category'] ?? 'general'
                );

            case 'list':
                $count = $result['count'] ?? 0;
                $total = $result['total'] ?? 0;

                if ($count === 0) {
                    return "You don't have any expenses recorded yet.";
                }

                $response = sprintf(
                    "Here's a summary of your expenses:\n\n".
                    "📊 Total: $%.2f\n".
                    '📝 Number of expenses: %d',
                    $total,
                    $count
                );

                if (isset($result['expenses']) && is_array($result['expenses'])) {
                    $response .= "\n\nRecent expenses:";
                    foreach (array_slice($result['expenses'], 0, 5) as $expense) {
                        $response .= sprintf(
                            "\n• $%.2f - %s (%s)",
                            $expense['amount'] ?? 0,
                            $expense['description'] ?? 'N/A',
                            $expense['date'] ?? 'N/A'
                        );
                    }
                }

                return $response;

            case 'deleted':
                return '✅ The expense has been deleted successfully.';

            case 'updated':
                return '✅ The expense has been updated successfully.';

            default:
                return $result['message'] ?? 'Expense operation completed.';
        }
    }

    /**
     * Format database query responses
     */
    private function formatDatabaseResponse(array $result): string
    {
        if (isset($result['error'])) {
            return "I couldn't access the database: ".$result['error'];
        }

        $recordsCount = $result['records_affected'] ?? $result['count'] ?? 0;

        if ($recordsCount === 0) {
            return 'No records found matching your query.';
        }

        $response = "Found {$recordsCount} record".($recordsCount > 1 ? 's' : '').'.';

        if (isset($result['result']) && is_array($result['result'])) {
            $response .= "\n\nHere are the results:";
            foreach (array_slice($result['result'], 0, 5) as $record) {
                if (is_array($record)) {
                    $response .= "\n• ".$this->formatRecord($record);
                }
            }
        }

        return $response;
    }

    /**
     * Format a single database record
     */
    private function formatRecord(array $record): string
    {
        $parts = [];

        // Pick the most relevant fields to display
        $displayFields = ['name', 'title', 'email', 'amount', 'description', 'status'];

        foreach ($displayFields as $field) {
            if (isset($record[$field])) {
                $parts[] = $record[$field];
            }
        }

        return implode(' - ', $parts) ?: json_encode($record);
    }

    /**
     * Format search results
     */
    private function formatSearchResponse(array $result): string
    {
        $count = $result['count'] ?? 0;

        if ($count === 0) {
            return 'No results found for your search.';
        }

        $response = "Found {$count} result".($count > 1 ? 's' : '').' for your search.';

        if (isset($result['results']) && is_array($result['results'])) {
            $response .= "\n\nTop results:";
            foreach (array_slice($result['results'], 0, 5) as $item) {
                if (is_array($item)) {
                    $title = $item['title'] ?? $item['name'] ?? 'Untitled';
                    $snippet = $item['snippet'] ?? $item['description'] ?? '';
                    $response .= "\n\n📄 {$title}";
                    if ($snippet) {
                        $response .= "\n".substr($snippet, 0, 150).'...';
                    }
                }
            }
        }

        return $response;
    }

    /**
     * Format combined responses from multiple tools
     */
    private function formatCombinedResponse(array $result): string
    {
        if (isset($result['summary'])) {
            return $result['summary'];
        }

        $responses = [];

        if (isset($result['results']) && is_array($result['results'])) {
            foreach ($result['results'] as $toolName => $toolResult) {
                if (is_array($toolResult)) {
                    $formatted = $this->formatToNaturalLanguage(['formatted_result' => $toolResult]);
                    if ($formatted) {
                        $responses[] = $formatted;
                    }
                }
            }
        }

        return implode("\n\n", $responses) ?: "I've processed your request using multiple tools.";
    }

    /**
     * Format error responses
     */
    private function formatError(array $errorData): string
    {
        $error = $errorData['error'] ?? 'Unknown error';

        return "I encountered an issue: {$error}. Please try rephrasing your request or contact support if the issue persists.";
    }

    /**
     * Simple response formatting without AI
     */
    private function formatSimpleResponse(array $result): string
    {
        if (isset($result['response'])) {
            return $result['response'];
        }

        if (isset($result['message'])) {
            return $result['message'];
        }

        // Try to create a meaningful response from the data
        return "I've processed your request. ".json_encode($result);
    }

    /**
     * Format using AI when available
     */
    private function formatWithAI(array $data): string
    {
        try {
            $prompt = "Convert this JSON response to natural, friendly language:\n".
                     json_encode($data['formatted_result'], JSON_PRETTY_PRINT)."\n".
                     "Make it conversational and helpful. If it's expense data, format it nicely.";

            $userMessage = UserMessage::make($prompt);
            $response = $this->formatterAgent->chat($userMessage);

            return $response->getContent();
        } catch (\Exception $e) {
            Log::warning('AI formatting failed, using fallback', ['error' => $e->getMessage()]);

            return $this->formatSimpleResponse($data['formatted_result'] ?? []);
        }
    }

    /**
     * Create a fallback response when formatting fails
     */
    private function createFallbackResponse(array $data): Event
    {
        return new Event([
            'response' => 'I processed your request successfully.',
            'structured_data' => $data,
            'type' => 'chat_response',
            'tools_used' => $data['tools_used'] ?? [],
            'metadata' => $data['metadata'] ?? [],
        ]);
    }

    /**
     * Get the formatter prompt for AI
     */
    private function getFormatterPrompt(): string
    {
        return <<<'PROMPT'
        You are a response formatter that converts structured JSON data
        into natural, conversational responses.

        Guidelines:
        - Be friendly and professional
        - Explain technical details in simple terms
        - If there were errors, explain them clearly
        - If multiple tools were used, summarize all results coherently
        - Format lists and data in a readable way
        - For expenses, use currency formatting and clear descriptions
        - Keep responses concise but informative
        - Use appropriate emoji sparingly for clarity (✅ for success, 📊 for data, etc.)
        PROMPT;
    }
}
