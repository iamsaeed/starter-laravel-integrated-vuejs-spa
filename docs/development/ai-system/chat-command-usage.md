# Chat Command Usage Guide

The `php artisan chat` command allows you to test the AI-powered chat workflow system from the command line.

## Features

- **Single Message Mode**: Send one message and get a response
- **Interactive Mode**: Have a conversation with the AI
- **Multi-Agent Routing**: Automatically selects the right tool based on intent
- **Colored Output**: Different colors for different response types
- **Execution Metrics**: See how long each request takes

## Usage

### 1. Basic Usage (Single Message)

```bash
php artisan chat "How do I get started?"
```

### 2. Interactive Mode

```bash
php artisan chat --interactive
```

This opens an interactive chat session. Type your messages and press Enter. Type `exit`, `quit`, or `q` to stop.

### 3. With User/Workspace Options

```bash
php artisan chat "Add expense $25 for lunch" --user=2 --workspace=3
```

### 4. Prompted Input

```bash
php artisan chat
# Will prompt you to enter a message
```

## Examples

### Conversation Tool
```bash
php artisan chat "What can you help me with?"
```
**Output:**
```
🤖 AI Chat Workflow - MVP Demo

Processing message for User: Admin User (ID: 1)

🔍 Analyzing intent...

🛠️  Tools Used: conversation

💬 AI Response:
   I can help you with various tasks including managing expenses, querying data,
   searching information, and answering general questions.

⏱️  Execution time: 0.234s
```

### Expense Tool
```bash
php artisan chat "Add expense $15 for coffee"
```
**Output:**
```
🛠️  Tools Used: expense

💰 Expense:
   Expense added successfully
   Amount: $15.00
   Description: coffee
```

### Database Tool
```bash
php artisan chat "Show me all users"
```
**Output:**
```
🛠️  Tools Used: database

🗄️  Database Query:
   Found 10 users in the system
   Records found: 10
```

### Search Tool
```bash
php artisan chat "Search for Laravel documentation"
```
**Output:**
```
🛠️  Tools Used: search

🔍 Search Results:
   Search completed for: Laravel documentation
   1. Laravel Documentation - Getting Started
   2. Laravel 12 Release Notes
   3. Laravel Installation Guide
```

### Multiple Tools
```bash
php artisan chat "Add a $50 expense for dinner and email me the report"
```
**Output:**
```
🛠️  Tools Used: expense, conversation

🔄 Combined Results:
   Expense added successfully. Report will be emailed to you shortly.
```

## Interactive Mode Example

```bash
$ php artisan chat --interactive

🤖 AI Chat Workflow - MVP Demo

🔄 Interactive mode - type "exit" or "quit" to stop

💬 You: Hello!

Processing message for User: Admin User (ID: 1)

🔍 Analyzing intent...

🛠️  Tools Used: conversation

💬 AI Response:
   Hello! How can I help you today?

⏱️  Execution time: 0.189s

💬 You: Add expense $20 for taxi

Processing message for User: Admin User (ID: 1)

🔍 Analyzing intent...

🛠️  Tools Used: expense

💰 Expense:
   Expense added successfully
   Amount: $20.00
   Description: taxi

⏱️  Execution time: 0.156s

💬 You: exit

👋 Goodbye!
```

## Command Options

| Option | Description | Default |
|--------|-------------|---------|
| `message` | The message to send (optional) | Prompts if not provided |
| `--user=ID` | User ID to use for the request | 1 |
| `--workspace=ID` | Workspace ID to use for the request | 1 |
| `--interactive` | Run in interactive chat mode | false |
| `--verbose` | Show detailed error traces | false |

## Tool Selection Logic

The system automatically selects tools based on keywords in your message:

- **Expense Tool**: `expense`, `spend`, `cost`, `pay`, `bill`, `$`, `€`, `£`
- **Database Tool**: `show all`, `list`, `display`, `users`, `workspace`, `data`, `get all`
- **Search Tool**: `search for`, `look for`, `find information`, `documentation`
- **Conversation Tool**: Default fallback for general questions

## Requirements

- Valid OpenAI API key in `.env` (`OPENAI_API_KEY`)
- At least one user in the database
- Configured NeuronAI settings in `config/neuron.php`

## Configuration

The command uses the `MultiAgentRouterNode` which reads from `config/neuron.php`:

```php
'workflows' => [
    'chat' => [
        'enabled_tools' => [
            'conversation' => true,
            'expense' => true,
            'database' => true,
            'search' => true,
        ],
    ],
],
```

Enable or disable tools by modifying this configuration.

## Troubleshooting

### Error: User with ID X not found
**Solution**: Ensure you have users in your database or specify a valid user ID with `--user=ID`

### Error: Cannot initialize MultiAgentRouterNode
**Solution**: Check that your OpenAI API key is valid in `.env`

### Timeout/Slow Response
**Solution**: This is normal for first request. OpenAI API calls take time. Subsequent requests should be faster.

### No response or empty output
**Solution**: Run with `--verbose` to see detailed error messages:
```bash
php artisan chat "test message" --verbose
```

## Testing Different Scenarios

```bash
# Test conversation
php artisan chat "What is Laravel?"

# Test expense tracking
php artisan chat "I spent $45 on groceries"

# Test database queries
php artisan chat "Show me all workspaces"

# Test search
php artisan chat "Find documentation about Eloquent"

# Test multi-tool
php artisan chat "Add $30 expense and show my total"

# Test error handling
php artisan chat ""
```

## Notes

- The command currently works with the central database for demo purposes
- In production, it should switch to tenant context based on the workspace ID
- Tool responses are mocked in tests but use real implementations in the command
- Execution time includes AI processing but not database queries
