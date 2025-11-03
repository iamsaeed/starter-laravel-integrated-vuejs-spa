# AI Chat Workflow System - MVP

This directory contains documentation for the AI-powered chat workflow system built with NeuronAI and OpenAI.

## Overview

The MVP implements a code-based (not database-driven) workflow system that uses multi-agent routing to intelligently handle user messages by selecting and executing the appropriate tools.

## Architecture

### Core Components

1. **ChatWorkflow** ([app/Neuron/Workflows/ChatWorkflow.php](../../app/Neuron/Workflows/ChatWorkflow.php))
   - Main workflow orchestrator
   - Connects three nodes in sequence: Input → Router → Formatter

2. **MultiAgentRouterNode** ([app/Neuron/Nodes/MultiAgentRouterNode.php](../../app/Neuron/Nodes/MultiAgentRouterNode.php))
   - Core routing logic
   - Analyzes user intent using keyword matching
   - Executes appropriate tools based on detected intent
   - Combines results from multiple tools when needed

3. **Tools**
   - **ConversationTool**: General chat and Q&A
   - **ExpenseTool**: Add, list, update, delete expenses
   - **DatabaseQueryTool**: Query database records
   - **SearchEngineTool**: Search documentation and information

4. **ChatEvent** ([app/Neuron/Events/ChatEvent.php](../../app/Neuron/Events/ChatEvent.php))
   - Event data container
   - Implements NeuronAI\Workflow\Event interface
   - Provides convenient data access methods

## Files & Directories

```
app/
├── Console/Commands/
│   └── ChatCommand.php           # CLI interface for testing
├── Http/Controllers/Api/Workspace/
│   └── ChatController.php        # API endpoint for chat
├── Models/
│   ├── ChatConversation.php      # Chat conversation model
│   ├── ChatMessage.php           # Chat message model
│   └── Expense.php               # Expense model
├── Neuron/
│   ├── Events/
│   │   └── ChatEvent.php         # Event implementation
│   ├── Nodes/
│   │   ├── ChatInputNode.php     # Input validation node
│   │   ├── MultiAgentRouterNode.php  # Main routing node
│   │   └── ResponseFormatterNode.php # Response formatting
│   ├── Tools/
│   │   ├── ConversationTool.php  # General chat
│   │   ├── ExpenseTool.php       # Expense management
│   │   ├── DatabaseQueryTool.php # Database queries
│   │   └── SearchEngineTool.php  # Search functionality
│   └── Workflows/
│       └── ChatWorkflow.php      # Workflow definition
├── Services/
│   └── ChatService.php           # Business logic layer
config/
└── neuron.php                    # NeuronAI configuration
database/migrations/tenant/
├── 2025_10_11_000001_create_chat_conversations_table.php
├── 2025_10_11_000002_create_chat_messages_table.php
└── 2025_10_11_000003_create_expenses_table.php
tests/
├── Feature/

## Quick Start

**📖 For detailed step-by-step instructions, see [QUICKSTART.md](QUICKSTART.md)**

### 1. Run Setup Command (Recommended)

```bash
# Seed database (if not already done)
php artisan db:seed

# Setup workspace and modules automatically
php artisan chat:setup
```

This will:
- ✅ Verify test user exists (ID: 2 from seeders)
- ✅ Create "Test Workspace" 
- ✅ Add Expenses and Tasks modules
- ✅ Initialize tenant database
- ✅ Display your User ID and Workspace ID
- ✅ Give you exact commands to run

### 2. Start Chatting

Use the commands provided by the setup:

```bash
php artisan chat --interactive --user=2 --workspace=3
```

**That's it! See [QUICKSTART.md](QUICKSTART.md) for detailed guide and examples.**
```

### 4. Test with CLI

```bash
# Single message
php artisan chat "How do I get started?"

# Interactive mode
php artisan chat --interactive

# With specific user
php artisan chat "Add expense $25 for lunch" --user=2
```

### 5. Test with API

```bash
POST /api/workspaces/{workspace}/chat
Headers:
  Authorization: Bearer {token}
  Accept: application/json
Body:
  {
    "message": "Add expense $15 for coffee",
    "conversation_id": "optional-uuid"
  }
```

## Features

### ✅ Implemented (MVP)

- [x] Multi-agent router node with intent classification
- [x] Tool-based architecture (4 tools)
- [x] Tenant-aware database isolation
- [x] CLI command for testing
- [x] API endpoint for frontend integration
- [x] Conversation history tracking
- [x] Expense management via natural language
- [x] Comprehensive unit tests (10 passing)
- [x] Mock infrastructure for testing
- [x] Code-based workflow (no database configuration needed)

### 🚧 Future Enhancements

- [ ] Database-driven workflow builder (n8n-style)
- [ ] Visual workflow editor
- [ ] Custom node creation
- [ ] Workflow versioning
- [ ] A/B testing of workflows
- [ ] Analytics and metrics
- [ ] More tools (Email, Calendar, CRM, etc.)
- [ ] AI-powered intent classification (replace keyword matching)
- [ ] Streaming responses
- [ ] Voice input/output
- [ ] Multi-language support

## Testing

### Unit Tests

```bash
# Run all unit tests
php artisan test --filter=MultiAgentRouterNodeTest

# Run with coverage
php artisan test --coverage
```

**Status**: ✅ 10/10 passing (16 assertions)

### Feature Tests

```bash
# Run feature tests
php artisan test --filter=TenantChatWorkflowTest
```

**Status**: ⚠️ Require mocking for OpenAI API calls

### Manual Testing

Use the CLI command for manual testing:
```bash
php artisan chat --interactive
```

## API Endpoints

### Send Chat Message

```
POST /api/workspaces/{workspace}/chat
```

**Request:**
```json
{
  "message": "Add expense $15 for coffee",
  "conversation_id": "optional-uuid"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "message_id": "uuid",
    "conversation_id": "uuid",
    "response": "Expense added successfully",
    "tools_used": ["expense"],
    "metadata": {
      "execution_time": 0.234,
      "tokens_used": 150
    }
  }
}
```

### Get Conversation History

```
GET /api/workspaces/{workspace}/conversations/{conversation}
```

### List Conversations

```
GET /api/workspaces/{workspace}/conversations
```

## Configuration

### Tool Enablement

Tools can be enabled/disabled in `config/neuron.php`:

```php
'enabled_tools' => [
    'conversation' => true,  // General chat
    'expense' => true,       // Expense management
    'database' => false,     // Disable database queries
    'search' => false,       // Disable search
],
```

### Intent Classification

Currently uses keyword-based matching. Keywords defined in [MultiAgentRouterNode.php](../../app/Neuron/Nodes/MultiAgentRouterNode.php):

```php
// Expense keywords
'expense', 'spend', 'cost', 'pay', '$', '€', '£'

// Database keywords
'show all', 'list', 'display', 'users', 'workspace'

// Search keywords
'search for', 'look for', 'find information'
```

## Troubleshooting

### Common Issues

1. **OpenAI API Errors**
   - Verify API key in `.env`
   - Check OpenAI account credits
   - Ensure API key has correct permissions

2. **Tenant Database Not Found**
   - Run tenant migrations: `php artisan tenants:migrate`
   - Verify tenant is created in database

3. **Tool Not Executing**
   - Check tool is enabled in `config/neuron.php`
   - Verify keyword matching patterns
   - Check logs: `storage/logs/laravel.log`

4. **Tests Timing Out**
   - Mock OpenAI calls in tests
   - Use `MocksNeuronAI` trait
   - Increase test timeout if needed

### Debug Mode

Run commands with verbose output:
```bash
php artisan chat "test" --verbose
```

Check logs:
```bash
tail -f storage/logs/laravel.log
```

## Performance

### Typical Response Times

- Conversation Tool: ~0.2s (OpenAI API call)
- Expense Tool: ~0.05s (local processing)
- Database Tool: ~0.1s (database query)
- Search Tool: ~0.15s (local search)

### Optimization Tips

1. Enable caching for frequent queries
2. Use streaming for long responses
3. Implement request queuing for high load
4. Cache OpenAI responses for common questions
5. Use database indexing for search

## Security

### Implemented

- ✅ Sanctum authentication required
- ✅ Tenant isolation (workspace-based)
- ✅ Input validation and sanitization
- ✅ Rate limiting on API endpoints

### Best Practices

- Never expose OpenAI API key to frontend
- Validate all user inputs
- Sanitize data before database operations
- Log all chat interactions for audit
- Implement content filtering for inappropriate messages

## Contributing

When adding new features:

1. Write tests first (TDD approach)
2. Update documentation
3. Run `vendor/bin/pint` for code formatting
4. Ensure all tests pass
5. Update this README if needed

## License

This is part of the Laravel starter application.

## Support

For issues or questions:
1. Check documentation in this directory
2. Review test files for examples
3. Check `storage/logs/laravel.log`
4. Create an issue in the project repository
