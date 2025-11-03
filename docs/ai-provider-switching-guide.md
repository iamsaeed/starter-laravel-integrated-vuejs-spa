# AI Provider Switching Guide

This guide explains how to easily switch between different AI providers (OpenAI, Anthropic, Gemini, DeepSeek) in your Neuron AI system.

## Quick Start

### Option 1: Global Provider Switch (Recommended)

Change one line in your `.env` file:

```bash
# Use DeepSeek for all agents
AI_PROVIDER=deepseek

# Or use Anthropic (Claude)
AI_PROVIDER=anthropic

# Or use Google Gemini
AI_PROVIDER=gemini

# Or use OpenAI
AI_PROVIDER=openai
```

**Important:** After changing `.env`, always run:
```bash
php artisan config:clear
```

### Option 2: Per-Agent Override in Config

Edit `config/ai.php` to override specific agents:

```php
'agent_providers' => [
    'supervisor' => 'anthropic',  // Use Claude for routing decisions
    'code' => 'deepseek',         // Use DeepSeek for code generation
    'research' => 'gemini',       // Use Gemini for research tasks
    // Other agents will use the default provider
],
```

### Option 3: Code-Based Override

In any agent class, set the `$providerOverride` property:

```php
class CodeAgent extends Agent
{
    use HasAIProvider;

    // Force this agent to always use DeepSeek
    protected ?string $providerOverride = 'deepseek';
}
```

## Priority Order

The system determines which provider to use in this order:

1. **Agent property override** (`$providerOverride`) - Highest priority
2. **Config file override** (`config/ai.php` → `agent_providers`)
3. **Default provider** (`AI_PROVIDER` env variable)
4. **Fallback** (OpenAI if nothing is configured)

## Available Agents

You can configure different providers for these agents:

- `supervisor` - Routes requests to specialized agents
- `conversation` - General conversation and chat
- `code` - Code generation and debugging
- `research` - In-depth research with web search
- `search_engine` - Quick web searches
- `analysis` - Data analysis and insights
- `blog_writing` - Content creation for blogs
- `email_drafting` - Professional email composition
- `email_design` - HTML email template design
- `expense` - Expense tracking and management
- `workspace` - Workspace/tenant management
- `module` - Module installation and configuration

## Example Configurations

### Cost Optimization Strategy

Use cheaper models for simple tasks, powerful ones for complex tasks:

```bash
# .env
AI_PROVIDER=deepseek  # Default: cheap and fast
```

```php
// config/ai.php
'agent_providers' => [
    'code' => 'deepseek',      // Complex code needs reasoning
    'supervisor' => 'anthropic', // Smart routing decisions
    'conversation' => 'deepseek', // General chat - keep it cheap
],
```

### Quality-First Strategy

Use premium models for everything:

```bash
# .env
AI_PROVIDER=anthropic  # Claude for all agents
```

### Mixed Provider Strategy

Use different providers based on their strengths:

```php
// config/ai.php
'agent_providers' => [
    'code' => 'deepseek',        // DeepSeek excels at code
    'research' => 'gemini',      // Gemini has large context
    'supervisor' => 'anthropic',  // Claude for reasoning
    'conversation' => 'openai',   // ChatGPT for conversation
],
```

## Troubleshooting

### Error: "AI provider 'X' is not configured"

**Cause:** Missing API key or config cache issue.

**Solution:**
1. Check your `.env` file has the API key: `PROVIDER_API_KEY=...`
2. Clear config cache: `php artisan config:clear`
3. Restart your dev server if running

### Provider Not Switching

**Cause:** Config cache not cleared.

**Solution:**
```bash
php artisan config:clear
php artisan config:cache  # Optional: cache for production
```

### Agent Uses Wrong Provider

**Cause:** Per-agent override in config or code.

**Solution:**
1. Check `config/ai.php` → `agent_providers` array
2. Check agent class for `$providerOverride` property
3. Remember: code override > config override > default provider

## Verification

Test which provider an agent is using:

```bash
php artisan tinker
```

```php
$agent = new \App\Neuron\SupervisorAgent();
$reflection = new \ReflectionClass($agent);
$method = $reflection->getMethod('provider');
$method->setAccessible(true);
$provider = $method->invoke($agent);

echo get_class($provider);
// Output: NeuronAI\Providers\Deepseek\Deepseek
```

## Architecture

The system uses:

1. **AIProviderManager** (`app/Services/AIProviderManager.php`)
   - Central service for provider instantiation
   - Handles configuration resolution
   - Validates API keys

2. **HasAIProvider Trait** (`app/Neuron/Traits/HasAIProvider.php`)
   - Used by all agent classes
   - Automatically resolves provider based on config
   - Supports per-agent overrides

3. **Configuration** (`config/ai.php`)
   - Stores all provider configurations
   - Defines default provider
   - Maps agent-specific overrides

## Benefits

✅ **Single Source of Truth** - Change one variable to switch all agents
✅ **Flexible Overrides** - Different providers for different tasks
✅ **Cost Optimization** - Use cheap models where appropriate
✅ **Easy Testing** - Quickly test different providers
✅ **No Code Changes** - All configuration via `.env` and `config/ai.php`
✅ **Provider Fallback** - Easy switching if one provider has issues

## Best Practices

1. **Always clear config cache** after changing `.env` or `config/ai.php`
2. **Use defaults wisely** - Set a reliable, affordable default provider
3. **Override strategically** - Only override where it provides value
4. **Test after changes** - Verify agents use expected providers
5. **Monitor costs** - Different providers have different pricing
6. **Keep API keys secure** - Never commit `.env` to version control

## Support

For issues or questions:
- Check the error message for specific provider/agent details
- Verify API keys are set correctly in `.env`
- Ensure config cache is cleared
- Check `storage/logs/laravel.log` for detailed error messages
