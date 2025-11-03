# Chat Workflow - Quick Start Guide

Get up and running with the AI chat workflow in under 2 minutes!

## Prerequisites

Before you begin, ensure you have:

1. ✅ Database seeded with test data
2. ✅ OpenAI API key in `.env`
3. ✅ Tenant migrations run

## Step-by-Step Setup

### 1️⃣ Seed the Database

If you haven't already, run the database seeders:

```bash
php artisan db:seed
```

This will create:
- Default admin user (ID: 1)
- **Test user (ID: 2)** ← We'll use this one
- Sample data

### 2️⃣ Run the Setup Command

This single command will:
- ✅ Verify user exists (ID: 2 from seeders)
- ✅ Create "Test Workspace"
- ✅ Add Expenses and Tasks modules
- ✅ Initialize tenant database
- ✅ Give you the exact commands to run

```bash
php artisan chat:setup
```

**Expected Output:**
```
🚀 Setting up Chat Workflow Test Environment

📝 Step 1: Checking for test user...
   ✓ User: Test User (ID: 2)

🏢 Step 2: Setting up test workspace...
   ✓ Workspace: Test Workspace (ID: 3)

📦 Step 3: Setting up modules...
   ✓ Module: expenses
   ✓ Module: tasks

🗄️  Step 4: Initializing tenant database...
   ✓ Tenant database ready

═══════════════════════════════════════════════════════
✅ Setup Complete! You can now test the chat workflow.
═══════════════════════════════════════════════════════

📋 Test Configuration:
   User ID:      2
   User Name:    Test User
   User Email:   test@example.com
   Workspace ID: 3
   Workspace:    Test Workspace
   Modules:      expenses, tasks

🎯 Example Commands:

1. Test with default settings:
   php artisan chat --interactive --user=2 --workspace=3

2. Test expense tracking:
   php artisan chat "Add expense $25 for lunch" --user=2 --workspace=3

3. Test task management:
   php artisan chat "Create a task to review code" --user=2 --workspace=3

4. Test database queries:
   php artisan chat "Show me all my expenses" --user=2 --workspace=3

💡 Tip: Save these IDs for future use!
   User ID: 2
   Workspace ID: 3
```

### 3️⃣ Start Chatting!

Copy the interactive command from the output (it includes your user and workspace IDs):

```bash
php artisan chat --interactive --user=2 --workspace=3
```

### 4️⃣ Try These Messages

Once in interactive mode, try:

**💬 Conversation:**
```
You: Hello! What can you do?
```

**💰 Expense Tracking:**
```
You: Add expense $15 for coffee
You: I spent $45.50 on groceries
You: Add $20 expense for taxi
```

**📋 Task Management:**
```
You: Create a task to review pull request
You: Add task: Schedule team meeting
```

**🗄️ Database Queries:**
```
You: Show me all users
You: List my expenses
You: Show all workspaces
```

**🔍 Search:**
```
You: Find documentation about Laravel
You: Search for expense policy
```

**🔄 Multiple Tools:**
```
You: Add $30 expense and email me the receipt
```

Type `exit`, `quit`, or `q` to stop.

## Troubleshooting

### "No users found in database"

**Solution:** Run the seeders first:
```bash
php artisan db:seed
```

### "Workspace does not have tenant initialized"

**Solution:** Run tenant migrations:
```bash
php artisan tenants:migrate
```

### "OpenAI API key not configured"

**Solution:** Add your API key to `.env`:
```env
OPENAI_API_KEY=sk-your-api-key-here
```

### Starting Fresh

To reset and recreate the test workspace:
```bash
php artisan chat:setup --fresh
```

## What's Happening Behind the Scenes?

When you send a message:

1. **ChatInputNode** validates and cleans the input
2. **MultiAgentRouterNode** analyzes intent using keywords
3. **Tools are executed** based on detected intent:
   - `ConversationTool` - General chat
   - `ExpenseTool` - Expense operations
   - `DatabaseQueryTool` - Data queries
   - `SearchEngineTool` - Search functionality
4. **ResponseFormatterNode** formats the response
5. **Response displayed** with color-coded output

## Next Steps

- 📖 Read [Chat Command Usage](chat-command-usage.md) for advanced options
- 🏗️ Explore [MVP Documentation](mvp.md) for architecture details
- 🧪 Run tests: `php artisan test --filter=MultiAgentRouterNodeTest`
- 🔧 Customize tool selection in `app/Neuron/Nodes/MultiAgentRouterNode.php`

## Quick Reference

```bash
# Setup (first time)
php artisan db:seed
php artisan chat:setup
php artisan tenants:migrate

# Start chatting
php artisan chat --interactive --user=2 --workspace=3

# Single message
php artisan chat "Your message here" --user=2 --workspace=3

# Reset workspace
php artisan chat:setup --fresh
```

## Important Notes

⚠️ **User ID:** The seeder creates a test user with **ID: 2** (not 1). Always use `--user=2` unless you have a different user.

⚠️ **Workspace ID:** Will vary based on your database. The setup command shows you the exact ID to use.

⚠️ **Tenant Database:** Expenses and other tenant-specific data are stored in the tenant database. Make sure migrations are run.

⚠️ **OpenAI Costs:** Each message uses the OpenAI API and incurs costs. The conversation tool makes API calls.

---

**That's it! You're ready to test the AI chat workflow.** 🚀

Run `php artisan chat:setup` and follow the instructions!
