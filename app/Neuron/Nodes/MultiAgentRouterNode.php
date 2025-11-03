<?php

namespace App\Neuron\Nodes;

use App\Neuron\Events\ChatEvent;
use App\Neuron\Tools\ConversationTool;
use App\Neuron\Tools\DatabaseQueryTool;
use App\Neuron\Tools\ExpenseTool;
use App\Neuron\Tools\SearchAndAnswerTool;
use Illuminate\Support\Facades\Log;
use NeuronAI\Agent;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\Workflow\Event;
use NeuronAI\Workflow\Node;

/**
 * Core routing node that analyzes user intent and executes appropriate tools
 */
class MultiAgentRouterNode extends Node
{
    private Agent $orchestrator;

    private array $availableTools;

    private float $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);

        try {
            // Initialize the orchestrator agent with tool selection capability
            $provider = new OpenAI(
                config('neuron.providers.openai.api_key'),
                config('neuron.providers.openai.model', 'gpt-4o-mini')
            );
            // $provider = new Gemini(
            //     config('neuron.providers.gemini.api_key'),
            //     config('neuron.providers.gemini.model', 'gemini-1.5-pro')
            // );
            $this->orchestrator = Agent::make()
                ->withProvider($provider)
                ->withInstructions($this->getOrchestratorPrompt());

            // Register all available tools
            $this->availableTools = $this->initializeTools();
        } catch (\Exception $e) {
            Log::error('Failed to initialize MultiAgentRouterNode', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Initialize available tools based on configuration
     */
    private function initializeTools(): array
    {
        $tools = [];
        $enabledTools = config('neuron.workflows.chat.enabled_tools', []);

        if ($enabledTools['conversation'] ?? false) {
            $tools['conversation'] = new ConversationTool;
        }

        if ($enabledTools['database'] ?? false) {
            $tools['database'] = new DatabaseQueryTool;
        }

        if ($enabledTools['search'] ?? false) {
            $tools['search'] = new SearchAndAnswerTool;
        }

        if ($enabledTools['expense'] ?? false) {
            $tools['expense'] = new ExpenseTool;
        }

        return $tools;
    }

    /**
     * Handle the event and route to appropriate tools
     */
    public function handle(Event $event): Event
    {
        $userMessage = $event->get('message');
        $context = $event->get('context', []);

        try {
            // Emit progress: Analyzing intent
            $this->emitProgress('Analyzing intent...', $context);

            // Step 1: Analyze intent and select appropriate tool(s)
            $toolSelection = $this->selectTools($userMessage, $context);

            // Emit progress: Tools selected
            $toolNames = implode(', ', $toolSelection);
            $this->emitProgress("Using tools: {$toolNames}", $context);

            // Step 2: Execute selected tools
            $results = $this->executeTools($toolSelection, $userMessage, $context);

            // Emit progress: Processing results
            $this->emitProgress('Processing results...', $context);

            // Step 3: Combine results if multiple tools were used
            $combinedResult = $this->combineResults($results);

            // Step 4: Return structured JSON response
            return new ChatEvent([
                'original_message' => $userMessage,
                'tools_used' => array_keys($results),
                'raw_results' => $results,
                'formatted_result' => $combinedResult,
                'context' => $context,
                'metadata' => [
                    'execution_time' => $this->getExecutionTime(),
                    'tokens_used' => $this->getTokensUsed(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error in MultiAgentRouterNode', [
                'message' => $userMessage,
                'error' => $e->getMessage(),
            ]);

            // Fallback to conversation tool on error
            return $this->fallbackResponse($userMessage, $context, $e->getMessage());
        }
    }

    /**
     * Emit progress update to the frontend
     */
    private function emitProgress(string $message, array $context): void
    {
        // Get progress callback if available
        $progressCallback = $context['progress_callback'] ?? null;

        if ($progressCallback && is_callable($progressCallback)) {
            $progressCallback($message);
        }
    }

    /**
     * Select which tools to use based on the user message
     */
    private function selectTools(string $message, array $context): array
    {
        // Check if AI intent is enabled
        if (config('neuron.workflows.chat.use_ai_intent', false)) {
            // Hybrid mode: Fast path for obvious cases
            if (config('neuron.workflows.chat.intent_hybrid_mode', true)) {
                if ($this->hasObviousIntent($message)) {
                    return $this->selectToolsWithKeywords($message);
                }
            }

            // Smart path: AI for ambiguous cases
            return $this->selectToolsWithAI($message, $context);
        }

        // Fallback to keywords
        return $this->selectToolsWithKeywords($message);
    }

    /**
     * Use AI to select appropriate tools based on user intent
     */
    private function selectToolsWithAI(string $message, array $context): array
    {
        try {
            // Build tool descriptions from schemas
            $toolDescriptions = $this->buildToolDescriptions();

            // Create AI prompt for intent classification
            $prompt = $this->buildIntentPrompt($message, $toolDescriptions, $context);

            // Call AI orchestrator
            $userMessage = UserMessage::make($prompt);
            $response = $this->orchestrator->chat($userMessage)->getContent();

            // Extract JSON from response (handle markdown code blocks)
            $jsonString = $this->extractJsonFromResponse($response);

            // Parse JSON response
            $selection = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from AI: '.json_last_error_msg());
            }

            // Validate tools exist
            $tools = $this->validateToolSelection($selection);

            // Log for analysis
            $this->logIntentDecision($message, $selection, $tools);

            return $tools;

        } catch (\Exception $e) {
            // Fallback to keywords on error
            Log::warning('AI tool selection failed, using keyword fallback', [
                'message' => $message,
                'error' => $e->getMessage(),
            ]);

            return $this->selectToolsWithKeywords($message);
        }
    }

    /**
     * Extract JSON from AI response (handles markdown code blocks)
     */
    private function extractJsonFromResponse(string $response): string
    {
        // Remove markdown code blocks if present
        if (preg_match('/```(?:json)?\s*(\{.*\})\s*```/s', $response, $matches)) {
            return $matches[1];
        }

        // Try to find JSON object in the response
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            return $matches[0];
        }

        return $response;
    }

    /**
     * Build tool descriptions for AI
     */
    private function buildToolDescriptions(): array
    {
        $descriptions = [];

        foreach ($this->availableTools as $name => $tool) {
            $descriptions[$name] = [
                'name' => $name,
                'description' => $tool->getDescription(),
                'examples' => $this->getToolExamples($name),
            ];
        }

        return $descriptions;
    }

    /**
     * Get example usage for each tool
     */
    private function getToolExamples(string $toolName): array
    {
        return match ($toolName) {
            'expense' => [
                'Add expense $15 for coffee',
                'I spent $50 on groceries',
                'Track my spending',
                'What did I purchase yesterday?',
                'Show me my expenses',
                'List all my expenses',
            ],
            'database' => [
                'Show me all users',
                'Get all records',
                'List all settings',
            ],
            'search' => [
                'Search for Laravel docs',
                'Find information about AI',
                'Look for expense policy',
            ],
            'conversation' => [
                'Hello!',
                'What can you help with?',
                'How do I get started?',
                'Tell me about expense tracking',
            ],
            default => [],
        };
    }

    /**
     * Build AI prompt for intent classification
     */
    private function buildIntentPrompt(string $message, array $tools, array $context): string
    {
        $toolsJson = json_encode($tools, JSON_PRETTY_PRINT);

        return <<<PROMPT
You are an intelligent intent classifier for a chat system that HAS REAL INTERNET SEARCH CAPABILITY.

CRITICAL: This system HAS a working "search" tool that CAN search the internet using SERP API. You MUST use it when users want to search for information.

Available Tools:
{$toolsJson}

User Message: "{$message}"

IMPORTANT: Respond with ONLY a JSON object, no markdown formatting, no code blocks, just pure JSON:
{
    "tools": ["tool1", "tool2"],
    "reasoning": "Why these tools were selected",
    "confidence": 0.95
}

Guidelines:
1. For expense-related actions (adding, listing, tracking spending), use "expense"
2. For database queries (show users, display records, list settings), use "database"
3. For ANY search request (search, look up, find, latest news, etc), ALWAYS use "search" - DO NOT use "conversation"
4. For general conversation, greetings, or questions about the system itself, use "conversation"
5. You can select multiple tools if the request requires it
6. Confidence should be between 0 and 1

CRITICAL RULE FOR SEARCH:
- If the message contains ANY of these words: "search", "find", "look up", "latest", "news", "search for", "look for" → ALWAYS use "search" tool
- The "search" tool will perform REAL internet searches using SERP API
- NEVER use "conversation" for search requests - ALWAYS use "search" tool

Examples:
- "Add expense \$25 for lunch" → {"tools": ["expense"], "reasoning": "Direct expense addition request", "confidence": 1.0}
- "I need to track my spending" → {"tools": ["expense"], "reasoning": "Intent to track expenses", "confidence": 0.95}
- "What did I purchase yesterday?" → {"tools": ["expense"], "reasoning": "Query about past expenses", "confidence": 0.90}
- "Show me all users" → {"tools": ["database"], "reasoning": "Database query for users", "confidence": 1.0}
- "Search for Laravel documentation" → {"tools": ["search"], "reasoning": "Explicit web search request", "confidence": 1.0}
- "search fot the latest news on trump" → {"tools": ["search"], "reasoning": "News search request", "confidence": 1.0}
- "serch for the date today" → {"tools": ["search"], "reasoning": "Search request despite typo", "confidence": 1.0}
- "Search the internet for today's date" → {"tools": ["search"], "reasoning": "Internet search requested", "confidence": 1.0}
- "Look up Python tutorials" → {"tools": ["search"], "reasoning": "Web search for information", "confidence": 1.0}
- "Find information about AI" → {"tools": ["search"], "reasoning": "Information search query", "confidence": 1.0}
- "latest news about technology" → {"tools": ["search"], "reasoning": "News search request", "confidence": 1.0}
- "Hello!" → {"tools": ["conversation"], "reasoning": "Greeting message", "confidence": 1.0}
- "How do I get started?" → {"tools": ["conversation"], "reasoning": "General question about the system", "confidence": 1.0}
- "Tell me about expense tracking" → {"tools": ["conversation"], "reasoning": "Asking for explanation, not web search", "confidence": 0.95}

Now analyze the user's message and respond with ONLY the JSON object.
PROMPT;
    }

    /**
     * Validate and extract tool names from AI selection
     */
    private function validateToolSelection(array $selection): array
    {
        if (! isset($selection['tools']) || ! is_array($selection['tools'])) {
            throw new \Exception('Invalid tool selection format');
        }

        $tools = [];
        $confidence = $selection['confidence'] ?? 1.0;
        $minConfidence = config('neuron.workflows.chat.intent_confidence_threshold', 0.5);

        // If confidence is too low, fallback to conversation
        if ($confidence < $minConfidence) {
            Log::warning('AI intent confidence below threshold', [
                'confidence' => $confidence,
                'threshold' => $minConfidence,
                'tools' => $selection['tools'],
            ]);

            return ['conversation'];
        }

        // Validate each tool exists
        foreach ($selection['tools'] as $toolName) {
            if (isset($this->availableTools[$toolName])) {
                $tools[] = $toolName;
            } else {
                Log::warning("AI selected non-existent tool: {$toolName}");
            }
        }

        // If no valid tools, default to conversation
        if (empty($tools)) {
            $tools[] = 'conversation';
        }

        return $tools;
    }

    /**
     * Log AI intent decision for analysis
     */
    private function logIntentDecision(string $message, array $selection, array $tools): void
    {
        if (! config('neuron.workflows.chat.intent_log_decisions', false)) {
            return;
        }

        Log::info('AI Intent Classification', [
            'message' => $message,
            'selected_tools' => $selection['tools'] ?? [],
            'reasoning' => $selection['reasoning'] ?? 'N/A',
            'confidence' => $selection['confidence'] ?? 0,
            'validated_tools' => $tools,
        ]);
    }

    /**
     * Check for obvious patterns (fast path for hybrid mode)
     */
    private function hasObviousIntent(string $message): bool
    {
        // Very explicit patterns that don't need AI analysis
        return preg_match('/^\$\d+/', $message) ||
               preg_match('/^add expense/i', $message) ||
               preg_match('/^create expense/i', $message) ||
               preg_match('/\b(search|find|look up|latest news|search for|look for|serch|finde)\b/i', $message) ||
               preg_match('/^show (me )?all/i', $message) ||
               preg_match('/^list (all|my)/i', $message);
    }

    /**
     * Keyword-based selection (renamed for clarity)
     */
    private function selectToolsWithKeywords(string $message): array
    {
        $toolsToUse = [];

        // Check for expense-related keywords
        if (preg_match('/\b(expense|spend|spent|cost|pay|paid|bill|receipt|dollar|\$|€|£)\b/i', $message)) {
            $toolsToUse[] = 'expense';
        }

        // Check for database/query keywords (more specific patterns)
        if (preg_match('/\b(show\s+(me\s+)?all|list\s+(all|my)?|display\s+|users?|data|records?|get\s+(all|my))\b/i', $message)) {
            $toolsToUse[] = 'database';
        }

        // Check for search keywords
        if (preg_match('/\b(search|find|look\s+for|look\s+up|latest|news|serch|finde)\b/i', $message)) {
            $toolsToUse[] = 'search';
        }

        // Check for email/communication keywords (uses conversation tool for now)
        if (preg_match('/\b(email|send|report|notify|message)\b/i', $message) && ! in_array('conversation', $toolsToUse)) {
            $toolsToUse[] = 'conversation';
        }

        // Default to conversation if no specific tool matches
        if (empty($toolsToUse)) {
            $toolsToUse[] = 'conversation';
        }

        return $toolsToUse;
    }

    /**
     * Execute the selected tools
     */
    private function executeTools(array $toolNames, string $message, array $context): array
    {
        $results = [];

        foreach ($toolNames as $toolName) {
            if (isset($this->availableTools[$toolName])) {
                try {
                    // Emit progress before executing tool
                    $toolLabel = ucfirst($toolName);
                    $this->emitProgress("Executing {$toolLabel} tool...", $context);

                    $tool = $this->availableTools[$toolName];
                    $results[$toolName] = $tool->execute($message, $context);
                } catch (\Exception $e) {
                    Log::error("Error executing tool {$toolName}", [
                        'error' => $e->getMessage(),
                    ]);
                    $results[$toolName] = [
                        'error' => "Failed to execute {$toolName} tool: ".$e->getMessage(),
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Combine results from multiple tools
     */
    private function combineResults(array $results): array
    {
        // If only one tool was used, return its result directly
        if (count($results) === 1) {
            return reset($results);
        }

        // Combine multiple tool results
        return [
            'type' => 'combined',
            'results' => $results,
            'summary' => $this->generateSummary($results),
        ];
    }

    /**
     * Generate a summary of multiple tool results
     */
    private function generateSummary(array $results): string
    {
        $summaryParts = [];

        foreach ($results as $toolName => $result) {
            if (isset($result['message'])) {
                $summaryParts[] = $result['message'];
            } elseif (isset($result['response'])) {
                $summaryParts[] = $result['response'];
            }
        }

        return implode(' ', $summaryParts);
    }

    /**
     * Create a fallback response when there's an error
     */
    private function fallbackResponse(string $message, array $context, string $error): Event
    {
        // Try to use conversation tool as fallback
        if (isset($this->availableTools['conversation'])) {
            try {
                $result = $this->availableTools['conversation']->execute($message, $context);

                return new ChatEvent([
                    'original_message' => $message,
                    'tools_used' => ['conversation'],
                    'raw_results' => ['conversation' => $result],
                    'formatted_result' => $result,
                    'context' => $context,
                    'metadata' => [
                        'execution_time' => $this->getExecutionTime(),
                        'fallback' => true,
                        'error' => $error,
                    ],
                ]);
            } catch (\Exception $e) {
                // If even conversation fails, return a basic error response
            }
        }

        return new ChatEvent([
            'original_message' => $message,
            'tools_used' => [],
            'formatted_result' => [
                'type' => 'error',
                'response' => 'I apologize, but I encountered an error processing your request. Please try again or contact support if the issue persists.',
            ],
            'context' => $context,
            'metadata' => [
                'execution_time' => $this->getExecutionTime(),
                'error' => $error,
            ],
        ]);
    }

    /**
     * Get the orchestrator prompt
     */
    private function getOrchestratorPrompt(): string
    {
        return <<<'PROMPT'
        You are an intelligent router that analyzes user messages and determines
        which tools should be used to handle the request.

        Available tools:
        1. conversation - General chat and questions
        2. database - Query or modify database records
        3. search - Search documents or information
        4. expense - Add, list, or manage expenses

        Analyze the intent carefully and select appropriate tools.
        You can select multiple tools if needed.
        Always return your selection as a JSON array.

        Examples:
        - "Add expense of $15 for coffee" → ["expense"]
        - "Show my expenses and email report" → ["expense", "email"]
        - "How do I get started?" → ["conversation"]
        - "List all users" → ["database"]
        - "Search for expense policy" → ["search"]
        PROMPT;
    }

    /**
     * Get execution time in seconds
     */
    private function getExecutionTime(): float
    {
        return round(microtime(true) - $this->startTime, 3);
    }

    /**
     * Get tokens used (placeholder for actual implementation)
     */
    private function getTokensUsed(): int
    {
        // This would integrate with the AI provider to get actual token count
        // For now, return a placeholder
        return 0;
    }
}
