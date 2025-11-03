# 🚀 MVP: Simple Multi-Agent Chat System

## Overview

A simplified MVP implementation for a code-based workflow with a single multi-agent node that routes chat inputs to appropriate tools and returns natural language responses. This integrates with the existing Chat.vue frontend component.

## 🎯 MVP Scope

Since the frontend chat interface already exists at `resources/js/pages/user/Chat.vue`, this MVP focuses on:
1. Backend multi-agent router implementation
2. Tool/agent system with Neuron-AI
3. API endpoints to connect with existing frontend
4. Natural language processing pipeline

## 📐 Simplified Architecture

```
┌──────────────────────────────────────────────────┐
│     Existing Chat.vue Frontend Component         │
│  (resources/js/pages/user/Chat.vue)              │
└─────────────────┬────────────────────────────────┘
                  │ HTTP POST /api/chat
                  ▼
┌──────────────────────────────────────────────────┐
│            Chat API Controller                   │
│         (App\Http\Controllers\Api)               │
└─────────────────┬────────────────────────────────┘
                  │
                  ▼
┌──────────────────────────────────────────────────┐
│         Multi-Agent Router Node                  │
│        (Single Smart Node Approach)              │
│                                                  │
│  1. Intent Classification                        │
│  2. Tool Selection Logic                         │
│  3. Context Management                           │
└─────────────────┬────────────────────────────────┘
                  │
                  ▼
┌──────────────────────────────────────────────────┐
│            Available Tools/Agents                │
├──────────────────────────────────────────────────┤
│  • Conversation Agent (General chat)             │
│  • Database Agent (CRUD operations)              │
│  • Search Agent (Web/document search)            │
│  • Analytics Agent (Data analysis)               │
│  • Email Agent (Send/draft emails)               │
│  • Task Agent (Create/manage tasks)              │
│  • Expense Agent (Manage expenses)               │
└─────────────────┬────────────────────────────────┘
                  │
                  ▼
┌──────────────────────────────────────────────────┐
│           JSON Response Processing               │
│                                                  │
│  • Parse structured output                       │
│  • Format to natural language                    │
│  • Add context/explanations                      │
└─────────────────┬────────────────────────────────┘
                  │
                  ▼
┌──────────────────────────────────────────────────┐
│         Natural Language Response                │
│            (Back to Chat.vue)                    │
└──────────────────────────────────────────────────┘
```

## 💻 Implementation

### 1. Simple Code-Based Workflow

```php
namespace App\Neuron\Workflows;

use NeuronAI\Workflow\Workflow;
use NeuronAI\Workflow\Edge;
use NeuronAI\Workflow\Event;

/**
 * Simple code-based workflow for MVP
 * No database configuration needed - all logic in code
 */
class ChatWorkflow extends Workflow
{
    public function nodes(): array
    {
        return [
            new ChatInputNode(),           // Receives user message
            new MultiAgentRouterNode(),    // Smart routing logic
            new ResponseFormatterNode(),    // JSON to natural language
        ];
    }

    public function edges(): array
    {
        return [
            new Edge(ChatInputNode::class, MultiAgentRouterNode::class),
            new Edge(MultiAgentRouterNode::class, ResponseFormatterNode::class),
        ];
    }
}
```

### 2. Multi-Agent Router Node (Core Logic)

```php
namespace App\Neuron\Nodes;

use NeuronAI\Agent\Agent;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\Event;
use NeuronAI\Providers\OpenAI\OpenAI;
use App\Neuron\Tools\{
    ConversationTool,
    DatabaseQueryTool,
    SearchEngineTool,
    EmailTool,
    TaskManagementTool,
    ExpenseTool
};

class MultiAgentRouterNode extends Node
{
    private Agent $orchestrator;
    private array $availableTools;

    public function __construct()
    {
        // Initialize the orchestrator agent with tool selection capability
        $this->orchestrator = new Agent(
            provider: new OpenAI(config('services.openai.key')),
            systemPrompt: $this->getOrchestratorPrompt()
        );

        // Register all available tools
        $this->availableTools = [
            'conversation' => new ConversationTool(),
            'database' => new DatabaseQueryTool(),
            'search' => new SearchEngineTool(),
            'email' => new EmailTool(),
            'tasks' => new TaskManagementTool(),
            'expense' => new ExpenseTool(), // For expense management mentioned in Chat.vue
        ];
    }

    public function handle(Event $event): Event
    {
        $userMessage = $event->get('message');
        $context = $event->get('context', []);

        // Step 1: Analyze intent and select appropriate tool(s)
        $toolSelection = $this->selectTools($userMessage, $context);

        // Step 2: Execute selected tools
        $results = $this->executeTools($toolSelection, $userMessage, $context);

        // Step 3: Combine results if multiple tools were used
        $combinedResult = $this->combineResults($results);

        // Step 4: Return structured JSON response
        return new Event([
            'original_message' => $userMessage,
            'tools_used' => array_keys($results),
            'raw_results' => $results,
            'formatted_result' => $combinedResult,
            'metadata' => [
                'execution_time' => $this->getExecutionTime(),
                'tokens_used' => $this->getTokensUsed(),
            ]
        ]);
    }

    private function selectTools(string $message, array $context): array
    {
        // Use AI to determine which tools are needed
        $prompt = "Given this user message, determine which tools to use:\n" .
                 "Message: {$message}\n" .
                 "Available tools: " . json_encode(array_keys($this->availableTools)) . "\n" .
                 "Return a JSON array of tool names needed.";

        $response = $this->orchestrator->ask($prompt);
        return json_decode($response, true);
    }

    private function executeTools(array $toolNames, string $message, array $context): array
    {
        $results = [];

        foreach ($toolNames as $toolName) {
            if (isset($this->availableTools[$toolName])) {
                $tool = $this->availableTools[$toolName];
                $results[$toolName] = $tool->execute($message, $context);
            }
        }

        return $results;
    }

    private function combineResults(array $results): array
    {
        // Combine multiple tool results into a coherent response
        if (count($results) === 1) {
            return reset($results);
        }

        return [
            'type' => 'combined',
            'results' => $results,
            'summary' => $this->generateSummary($results)
        ];
    }

    private function getOrchestratorPrompt(): string
    {
        return <<<PROMPT
        You are an intelligent router that analyzes user messages and determines
        which tools should be used to handle the request.

        Available tools:
        1. conversation - General chat and questions
        2. database - Query or modify database records
        3. search - Search web or documents
        4. email - Send or draft emails
        5. tasks - Create or manage tasks
        6. expense - Add, list, or manage expenses

        Analyze the intent carefully and select appropriate tools.
        You can select multiple tools if needed.
        Always return your selection as a JSON array.

        Examples:
        - "Add expense of $15 for coffee" → ["expense"]
        - "Show my expenses and email report" → ["expense", "email"]
        - "How do I get started?" → ["conversation"]
        PROMPT;
    }
}
```

### 3. Tool Implementations

```php
// 1. Conversation Tool - Simple chat
namespace App\Neuron\Tools;

use NeuronAI\Agent\Agent;
use NeuronAI\Providers\OpenAI\OpenAI;

class ConversationTool implements Tool
{
    private Agent $chatAgent;

    public function execute(string $message, array $context): array
    {
        $this->chatAgent = new Agent(
            provider: new OpenAI(config('services.openai.key')),
            systemPrompt: "You are a helpful assistant for a business application. Be friendly and professional."
        );

        $response = $this->chatAgent->ask($message);

        return [
            'type' => 'conversation',
            'response' => $response,
            'confidence' => 0.95
        ];
    }
}

// 2. Database Query Tool - With permission checking
class DatabaseQueryTool implements Tool
{
    public function execute(string $message, array $context): array
    {
        // Parse the intent to understand what database operation is needed
        $operation = $this->parseOperation($message);

        // Check permissions
        if (!$this->hasPermission($context['user'], $operation)) {
            return [
                'type' => 'database',
                'error' => 'Permission denied',
                'operation' => $operation
            ];
        }

        // Execute the database operation (tenant-aware)
        $result = match($operation['type']) {
            'select' => $this->executeQuery($operation),
            'insert' => $this->executeInsert($operation),
            'update' => $this->executeUpdate($operation),
            'delete' => $this->executeDelete($operation),
            default => ['error' => 'Unknown operation']
        };

        return [
            'type' => 'database',
            'operation' => $operation,
            'result' => $result,
            'records_affected' => $result['count'] ?? 0
        ];
    }

    private function parseOperation(string $message): array
    {
        // Use AI to understand what database operation is requested
        $agent = new Agent(
            provider: new OpenAI(config('services.openai.key')),
            systemPrompt: "Parse database operations from natural language. Return JSON with type, table, conditions."
        );

        $prompt = "Convert this to a database operation: {$message}";
        $response = $agent->ask($prompt);

        return json_decode($response, true);
    }

    private function hasPermission($user, $operation): bool
    {
        // Check user permissions for the operation
        $table = $operation['table'] ?? null;
        $type = $operation['type'] ?? 'read';

        return $user->can("{$type}_{$table}");
    }
}

// 3. Expense Tool - Specific for expense management
class ExpenseTool implements Tool
{
    public function execute(string $message, array $context): array
    {
        $action = $this->parseExpenseAction($message);

        switch ($action['type']) {
            case 'add':
                return $this->addExpense($action, $context);
            case 'list':
                return $this->listExpenses($action, $context);
            case 'delete':
                return $this->deleteExpense($action, $context);
            case 'update':
                return $this->updateExpense($action, $context);
            default:
                return ['error' => 'Unknown expense action'];
        }
    }

    private function addExpense(array $action, array $context): array
    {
        // Add expense to database
        $expense = Expense::create([
            'user_id' => $context['user']->id,
            'workspace_id' => $context['workspace_id'],
            'amount' => $action['amount'],
            'description' => $action['description'],
            'category' => $action['category'] ?? 'general',
            'date' => $action['date'] ?? now(),
        ]);

        return [
            'type' => 'expense',
            'action' => 'added',
            'expense' => $expense->toArray(),
            'message' => "Added expense of ${$expense->amount} for {$expense->description}"
        ];
    }

    private function listExpenses(array $action, array $context): array
    {
        $query = Expense::where('workspace_id', $context['workspace_id'])
                       ->where('user_id', $context['user']->id);

        if (isset($action['period'])) {
            $query = $this->applyPeriodFilter($query, $action['period']);
        }

        $expenses = $query->get();
        $total = $expenses->sum('amount');

        return [
            'type' => 'expense',
            'action' => 'list',
            'expenses' => $expenses->toArray(),
            'total' => $total,
            'count' => $expenses->count(),
            'message' => "Found {$expenses->count()} expenses totaling ${$total}"
        ];
    }

    private function parseExpenseAction(string $message): array
    {
        $agent = new Agent(
            provider: new OpenAI(config('services.openai.key')),
            systemPrompt: "Parse expense commands. Return JSON with type (add/list/delete/update), amount, description, date, period."
        );

        $response = $agent->ask("Parse this expense command: {$message}");
        return json_decode($response, true);
    }
}

// 4. Search Engine Tool
class SearchEngineTool implements Tool
{
    public function execute(string $message, array $context): array
    {
        // Determine search scope
        $searchScope = $this->determineScope($message);

        $results = match($searchScope) {
            'web' => $this->searchWeb($message),
            'documents' => $this->searchDocuments($message, $context['workspace_id']),
            'database' => $this->searchDatabase($message, $context['workspace_id']),
            default => $this->searchAll($message, $context)
        };

        return [
            'type' => 'search',
            'scope' => $searchScope,
            'query' => $message,
            'results' => $results,
            'count' => count($results)
        ];
    }
}
```

### 4. Response Formatter Node

```php
namespace App\Neuron\Nodes;

use NeuronAI\Agent\Agent;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\Event;
use NeuronAI\Providers\OpenAI\OpenAI;

class ResponseFormatterNode extends Node
{
    private Agent $formatterAgent;

    public function handle(Event $event): Event
    {
        $jsonData = $event->all();

        // Initialize formatting agent
        $this->formatterAgent = new Agent(
            provider: new OpenAI(config('services.openai.key')),
            systemPrompt: $this->getFormatterPrompt()
        );

        // Convert JSON to natural language
        $naturalResponse = $this->formatToNaturalLanguage($jsonData);

        return new Event([
            'response' => $naturalResponse,
            'structured_data' => $jsonData,
            'type' => 'chat_response'
        ]);
    }

    private function formatToNaturalLanguage(array $data): string
    {
        // Handle different response types
        if (isset($data['formatted_result']['error'])) {
            return $this->formatError($data['formatted_result']);
        }

        $prompt = "Convert this JSON response to natural, friendly language:\n" .
                 json_encode($data['formatted_result'], JSON_PRETTY_PRINT) . "\n" .
                 "Make it conversational and helpful. If it's expense data, format it nicely.";

        return $this->formatterAgent->ask($prompt);
    }

    private function formatError(array $errorData): string
    {
        return "I encountered an issue: {$errorData['error']}. Please try rephrasing your request or contact support if the issue persists.";
    }

    private function getFormatterPrompt(): string
    {
        return <<<PROMPT
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
        PROMPT;
    }
}
```

### 5. API Controller (Connects to Existing Frontend)

```php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Neuron\Workflows\ChatWorkflow;
use NeuronAI\Workflow\Event;
use Illuminate\Support\Str;
use App\Models\ChatConversation;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    /**
     * Handle chat messages from the existing Chat.vue frontend
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'conversation_id' => 'nullable|string|uuid',
        ]);

        // Get or create conversation
        $conversation = $this->getOrCreateConversation(
            $validated['conversation_id'],
            $request->user()
        );

        // Save user message
        $userMessage = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => $validated['message'],
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]
        ]);

        try {
            // Initialize the workflow
            $workflow = new ChatWorkflow();

            // Create initial event with user context
            $event = new Event([
                'message' => $validated['message'],
                'context' => [
                    'user' => $request->user(),
                    'workspace_id' => $request->user()->current_workspace_id,
                    'conversation_id' => $conversation->id,
                    'timestamp' => now(),
                    'message_history' => $this->getRecentMessages($conversation->id),
                ]
            ]);

            // Run the workflow
            $result = $workflow->run($event);

            // Save assistant response
            $assistantMessage = ChatMessage::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $result->get('response'),
                'metadata' => [
                    'tools_used' => $result->get('tools_used', []),
                    'execution_time' => $result->get('metadata.execution_time'),
                    'tokens_used' => $result->get('metadata.tokens_used'),
                ]
            ]);

            // Return response matching Chat.vue expectations
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $assistantMessage->id,
                    'role' => 'assistant',
                    'content' => $assistantMessage->content,
                    'created_at' => $assistantMessage->created_at,
                    'tools_used' => $assistantMessage->metadata['tools_used'] ?? [],
                ],
                'conversation_id' => $conversation->id,
                'metadata' => [
                    'execution_time' => $result->get('metadata.execution_time'),
                ]
            ]);

        } catch (\Exception $e) {
            // Log error
            \Log::error('Chat workflow error', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
                'message' => $validated['message']
            ]);

            // Return error response
            return response()->json([
                'success' => false,
                'error' => 'Sorry, I encountered an error processing your request. Please try again.',
            ], 500);
        }
    }

    /**
     * Get conversation history (for sidebar)
     */
    public function conversations(Request $request)
    {
        $conversations = ChatConversation::where('user_id', $request->user()->id)
            ->where('workspace_id', $request->user()->current_workspace_id)
            ->orderBy('updated_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'conversations' => $conversations->map(function ($conv) {
                return [
                    'id' => $conv->id,
                    'title' => $conv->title ?? 'New Chat',
                    'last_message' => $conv->messages()->latest()->first()?->content,
                    'updated_at' => $conv->updated_at,
                ];
            })
        ]);
    }

    /**
     * Clear conversation
     */
    public function clearConversation(Request $request, $conversationId)
    {
        $conversation = ChatConversation::where('id', $conversationId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $conversation->messages()->delete();

        return response()->json(['success' => true]);
    }

    private function getOrCreateConversation($conversationId, $user)
    {
        if ($conversationId) {
            return ChatConversation::where('id', $conversationId)
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        return ChatConversation::create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'workspace_id' => $user->current_workspace_id,
            'title' => 'New Chat',
        ]);
    }

    private function getRecentMessages($conversationId, $limit = 10)
    {
        return ChatMessage::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(function ($msg) {
                return [
                    'role' => $msg->role,
                    'content' => $msg->content
                ];
            })
            ->values()
            ->toArray();
    }
}
```

### 6. Database Migrations

```php
// Create conversations table
Schema::create('chat_conversations', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignId('user_id')->constrained();
    $table->foreignId('workspace_id')->constrained();
    $table->string('title')->nullable();
    $table->json('context')->nullable();
    $table->timestamps();

    $table->index(['user_id', 'workspace_id']);
});

// Create messages table
Schema::create('chat_messages', function (Blueprint $table) {
    $table->id();
    $table->uuid('conversation_id');
    $table->string('role'); // 'user', 'assistant', 'system'
    $table->text('content');
    $table->json('metadata')->nullable();
    $table->timestamps();

    $table->foreign('conversation_id')
          ->references('id')
          ->on('chat_conversations')
          ->onDelete('cascade');

    $table->index('conversation_id');
});
```

### 7. Routes

```php
// routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    // Chat endpoints
    Route::post('/chat', [ChatController::class, 'chat']);
    Route::get('/chat/conversations', [ChatController::class, 'conversations']);
    Route::delete('/chat/conversations/{id}/clear', [ChatController::class, 'clearConversation']);
});
```

### 8. Configuration

```php
// config/neuron.php
return [
    'workflows' => [
        'chat' => [
            'class' => \App\Neuron\Workflows\ChatWorkflow::class,
            'enabled_tools' => [
                'conversation' => true,
                'database' => true,
                'search' => true,
                'expense' => true,
                'email' => false,  // Disabled for MVP
                'tasks' => false,   // Disabled for MVP
            ],
            'ai_provider' => env('AI_PROVIDER', 'openai'),
            'model' => env('AI_MODEL', 'gpt-4'),
            'max_tokens' => 2000,
            'temperature' => 0.7,
        ]
    ],

    'tools' => [
        'database' => [
            'allowed_tables' => ['users', 'projects', 'tasks', 'expenses'],
            'max_records' => 100,
            'allow_writes' => true,  // Enable for expense creation
        ],
        'search' => [
            'providers' => ['internal'],
            'max_results' => 10,
        ],
        'expense' => [
            'categories' => ['food', 'travel', 'office', 'entertainment', 'other'],
            'require_receipt' => false,
            'auto_categorize' => true,
        ]
    ],

    'security' => [
        'rate_limit' => 30,  // requests per minute
        'max_conversation_length' => 100,  // messages
        'require_authentication' => true,
        'log_all_interactions' => true,
    ]
];
```

### 9. Frontend Service Integration

```javascript
// resources/js/services/chatService.js
import api from '@/utils/api'

export const chatService = {
    async sendMessage(data) {
        const response = await api.post('/api/chat', data)
        return response.data
    },

    async getConversations() {
        const response = await api.get('/api/chat/conversations')
        return response.data
    },

    async clearConversation(conversationId) {
        const response = await api.delete(`/api/chat/conversations/${conversationId}/clear`)
        return response.data
    }
}
```

### 10. Update Chat.vue Component

```javascript
// Update the handleSendMessage method in Chat.vue
import { chatService } from '@/services/chatService'

const handleSendMessage = async (content) => {
    // Add user message to UI
    messages.value.push({
        id: Date.now(),
        role: 'user',
        content: content,
        created_at: new Date()
    })

    // Show typing indicator
    isTyping.value = true

    try {
        // Send to backend
        const response = await chatService.sendMessage({
            message: content,
            conversation_id: currentConversationId.value
        })

        // Add assistant response
        messages.value.push(response.message)

        // Update conversation ID
        currentConversationId.value = response.conversation_id

    } catch (error) {
        console.error('Chat error:', error)
        messages.value.push({
            id: Date.now(),
            role: 'assistant',
            content: 'Sorry, I encountered an error. Please try again.',
            created_at: new Date()
        })
    } finally {
        isTyping.value = false
    }
}

const handleExampleClick = (example) => {
    handleSendMessage(example)
}
```

## 🚀 Benefits of This MVP Approach

1. **Immediate Integration**: Works with existing Chat.vue frontend
2. **Simple Architecture**: Single workflow, no complex node connections
3. **Flexibility**: Easy to add/remove tools
4. **Fast Development**: Can be built in 1-2 weeks
5. **Production Ready**: This pattern is used in production AI systems
6. **Tenant-Aware**: Respects workspace isolation
7. **Extensible**: Clear path to add more capabilities

## 📈 Migration Path to Full System

```
MVP Phase 1 (Current):
Code-based workflow → Single multi-agent node → Existing Chat UI

Phase 2:
Add more tools → Improve routing logic → Add conversation memory

Phase 3:
Database configuration → Dynamic tool loading → Permission system

Phase 4:
Visual workflow builder → Multiple nodes → Complex workflows

Phase 5:
Full n8n-style system → Drag-drop interface → Template marketplace
```

## 🔑 Key Advantages

- **Working System Immediately**: Integrates with existing frontend
- **Real AI Capabilities**: Full Neuron-AI power
- **Expense Management**: Matches Chat.vue's promise of expense help
- **Clear Upgrade Path**: Not a throwaway prototype
- **Production-Viable**: Many companies use this pattern successfully

## 📝 Next Steps

1. Install Neuron-AI package
2. Create the workflow and node classes
3. Implement core tools (start with conversation and expense)
4. Set up API endpoints
5. Connect to existing Chat.vue frontend
6. Test with example prompts from the UI
7. Add more tools incrementally

This MVP provides a solid foundation that works with your existing frontend while allowing for future expansion into a full database-driven workflow system.