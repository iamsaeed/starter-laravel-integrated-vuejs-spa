# Multi-LLM Provider Integration Plan

## Overview
Enable support for multiple LLM providers (OpenAI, Anthropic, Gemini, DeepSeek, DigitalOcean) in your Neuron AI system with **per-user API key and model selection**. Each user can add their own API keys and choose their preferred models for each provider.

## Key Features:
✅ Users manage their own API keys (stored securely, encrypted)
✅ Users select their preferred LLM provider and model
✅ Per-user provider configuration (User A uses Claude, User B uses GPT-4)
✅ Fallback to system default if user hasn't configured their own keys
✅ Support for multiple providers: OpenAI, Anthropic, Gemini, DeepSeek, DigitalOcean

## Changes Required:

### 1. **Database Migration: User LLM Credentials**
Create a new `user_llm_credentials` table to store encrypted API keys per user:

**Migration: `create_user_llm_credentials_table`**
- `id` (bigint, primary key)
- `user_id` (bigint, foreign key to users)
- `provider` (string: 'openai', 'anthropic', 'gemini', 'deepseek', 'digitalocean')
- `api_key` (encrypted text - use Laravel's encryption)
- `model` (string: selected model for this provider, nullable)
- `base_url` (string: for custom endpoints like DigitalOcean, nullable)
- `is_active` (boolean: whether this provider is currently active for user)
- `metadata` (json: additional provider-specific config, nullable)
- `timestamps`
- Unique constraint: `user_id + provider`

### 2. **User Settings: Default Provider Selection**
Add new user settings for LLM configuration:

**Settings to add (in user settings):**
- `ai.default_provider` (string: 'openai', 'anthropic', etc.) - which provider to use by default
- `ai.fallback_to_system` (boolean: true) - whether to use system keys if user keys fail

### 3. **Model: `UserLlmCredential`**
Create `app/Models/UserLlmCredential.php`:
- Encrypted casting for `api_key` field
- Relationship to User model
- Methods:
  - `getDecryptedApiKey()`: Returns decrypted API key
  - `scopeActive()`: Filter only active credentials
  - `scopeForProvider(string $provider)`: Filter by provider

### 4. **Service: `UserLlmService`**
Create `app/Services/UserLlmService.php`:
- `getUserCredentials(User $user, string $provider)`: Get user's credentials for provider
- `saveCredentials(User $user, string $provider, array $data)`: Save/update user credentials
- `deleteCredentials(User $user, string $provider)`: Remove credentials
- `testConnection(User $user, string $provider)`: Test if credentials work
- `getAvailableProviders(User $user)`: List providers user has configured
- `getActiveProvider(User $user)`: Get user's active provider

### 5. **Update `app/Services/AIProviderFactory.php`**
- **NEW:** Factory now takes a `User` parameter
- Method: `make(User $user, ?string $providerName = null): AIProviderInterface`
- Logic:
  1. Determine provider: use `$providerName` OR user's default provider setting
  2. Fetch user's API key from `UserLlmCredential` for that provider
  3. If user has no key, fallback to system env keys (if `ai.fallback_to_system` is true)
  4. Instantiate provider with user's model preference OR provider default
  5. Handle provider-specific initialization (base_url for DigitalOcean, etc.)

### 6. **Update All 9 Agents**
Modify each agent's `provider()` method to pass the current user:
- ConversationAgent
- CodeAgent
- ResearchAgent
- BlogWritingAgent
- EmailDraftingAgent
- SearchEngineAgent
- AnalysisAgent
- EmailDesignAgent
- ExpenseAgent

Change from:
```php
new OpenAI(apiKey: config('ai.providers.openai.api_key'))
```

To:
```php
app(AIProviderFactory::class)->make($this->user)
```

### 7. **API Endpoints: User LLM Credentials Management**
Create `UserLlmCredentialController` with endpoints:

- `GET /api/user/llm-credentials` - List user's configured providers
- `POST /api/user/llm-credentials` - Add/update credentials for a provider
  - Body: `{ provider, api_key, model?, base_url?, is_active }`
- `DELETE /api/user/llm-credentials/{provider}` - Remove credentials
- `POST /api/user/llm-credentials/{provider}/test` - Test connection
- `GET /api/user/llm-credentials/available-models/{provider}` - List available models for provider

### 8. **Frontend: LLM Settings Page**
Create new settings page: `resources/js/pages/settings/LlmProvidersSettings.vue`

**UI Components:**
- List of available LLM providers (OpenAI, Anthropic, Gemini, DeepSeek, DigitalOcean)
- For each provider:
  - Toggle to enable/disable
  - Input field for API key (password type, masked)
  - Dropdown to select model from available models
  - "Test Connection" button
  - "Save" button
- Radio buttons to select default provider
- Toggle for "Fallback to system keys"

**Features:**
- Show which providers are configured (green checkmark)
- Show which provider is currently active (highlighted)
- Test API key before saving
- Mask API keys in UI (show only last 4 characters)
- Fetch available models dynamically when provider is selected

### 9. **Update `config/ai.php`**
Add provider configurations with available models:

```php
'providers' => [
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'), // System fallback
        'default_model' => env('OPENAI_MODEL', 'gpt-4o'),
        'available_models' => [
            'gpt-4o',
            'gpt-4o-mini',
            'gpt-4-turbo',
            'gpt-3.5-turbo',
        ],
    ],
    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'default_model' => env('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
        'available_models' => [
            'claude-3-5-sonnet-20241022',
            'claude-3-5-haiku-20241022',
            'claude-3-opus-20240229',
        ],
    ],
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'default_model' => env('GEMINI_MODEL', 'gemini-2.0-flash-exp'),
        'available_models' => [
            'gemini-2.0-flash-exp',
            'gemini-1.5-pro',
            'gemini-1.5-flash',
        ],
    ],
    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
        'default_model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
        'available_models' => [
            'deepseek-chat',
            'deepseek-coder',
        ],
    ],
    'digitalocean' => [
        'api_key' => env('DIGITALOCEAN_API_KEY'),
        'base_url' => env('DIGITALOCEAN_BASE_URL', 'https://api.digitalocean.com/v2/ai'),
        'default_model' => env('DIGITALOCEAN_MODEL', 'gpt-4o-mini'),
        'available_models' => [
            'gpt-4o-mini',
            'gpt-4o',
        ],
    ],
],
'supervisor' => [
    'provider' => 'openai',
    'model' => 'gpt-4o-mini', // Fast, cheap model for routing
],
```

### 10. **Update `.env.example`**
Add OPTIONAL system-wide API keys (used as fallback):

```env
# System-wide LLM API Keys (Optional - users can add their own)
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4o

ANTHROPIC_API_KEY=
ANTHROPIC_MODEL=claude-3-5-sonnet-20241022

GEMINI_API_KEY=
GEMINI_MODEL=gemini-2.0-flash-exp

DEEPSEEK_API_KEY=
DEEPSEEK_MODEL=deepseek-chat

DIGITALOCEAN_API_KEY=
DIGITALOCEAN_BASE_URL=https://api.digitalocean.com/v2/ai
DIGITALOCEAN_MODEL=gpt-4o-mini
```

### 11. **Security Considerations**
- ✅ Encrypt API keys in database using Laravel's `encrypted` cast
- ✅ Never expose decrypted API keys in API responses
- ✅ Rate limit credential testing endpoints
- ✅ Add policy to ensure users can only manage their own credentials
- ✅ Log credential access for security audit
- ✅ Consider adding 2FA requirement for changing LLM credentials

## Benefits:
✅ **User Privacy:** Each user manages their own API keys
✅ **Cost Control:** Users pay for their own API usage via their keys
✅ **Provider Choice:** User A can use Claude, User B can use GPT-4
✅ **Model Flexibility:** Users can select specific models per provider
✅ **System Fallback:** Optional system-wide keys for users who don't configure their own
✅ **Security:** API keys encrypted in database
✅ **Easy Testing:** Built-in connection testing before saving credentials
✅ **Multi-tenant Ready:** Perfect for SaaS applications

## Files to Create:

### Database & Models
- `database/migrations/YYYY_MM_DD_create_user_llm_credentials_table.php`
- `app/Models/UserLlmCredential.php`

### Services
- `app/Services/AIProviderFactory.php` (create)
- `app/Services/UserLlmService.php` (create)

### Controllers & Requests
- `app/Http/Controllers/Api/UserLlmCredentialController.php`
- `app/Http/Requests/SaveUserLlmCredentialRequest.php`

### Frontend
- `resources/js/pages/settings/LlmProvidersSettings.vue`
- `resources/js/services/llmCredentialService.js`
- `resources/js/components/settings/ProviderCredentialCard.vue` (optional component)

### Config & Routes
- Update `config/ai.php` (add provider configs with available models)
- Update `routes/api.php` (add LLM credential routes)
- Update `.env.example` (add optional system API keys)

## Files to Modify:

### Backend
- All 9 agent files in `app/Neuron/` (update `provider()` method to pass user)
- `app/Models/User.php` (add relationship to UserLlmCredential)
- `database/seeders/DefaultSettingsSeeder.php` (add ai.default_provider, ai.fallback_to_system)

### Frontend
- `resources/js/router/index.js` (add route for LLM settings page)
- Update existing settings navigation to include LLM Providers link

## Implementation Order:

1. **Database Layer** (Migration, Model, Seeders)
2. **Backend Services** (UserLlmService, AIProviderFactory)
3. **API Layer** (Controller, Requests, Routes)
4. **Agent Updates** (Update all 9 agents to use factory with user)
5. **Frontend Service** (llmCredentialService.js)
6. **Frontend UI** (Settings page component)
7. **Testing** (PHPUnit tests for all endpoints and services)

Ready to proceed?
