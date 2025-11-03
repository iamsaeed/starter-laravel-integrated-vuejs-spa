<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\Deepseek\Deepseek;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\OpenAI\OpenAI;

/**
 * AI Provider Manager Service
 *
 * Centralizes AI provider configuration and instantiation.
 * Supports easy switching between providers and per-agent overrides.
 */
class AIProviderManager
{
    /**
     * Get the AI provider instance for a specific agent.
     */
    public function getProvider(?string $agentKey = null, ?string $overrideProvider = null): AIProviderInterface
    {
        $providerName = $this->resolveProviderName($agentKey, $overrideProvider);

        return $this->createProvider($providerName);
    }

    /**
     * Resolve which provider to use based on agent key, override, or default.
     */
    protected function resolveProviderName(?string $agentKey, ?string $overrideProvider): string
    {
        // 1. Use explicit override if provided
        if ($overrideProvider !== null) {
            return $overrideProvider;
        }

        // 2. Check for agent-specific override in config
        if ($agentKey !== null && config("ai.agent_providers.{$agentKey}")) {
            return config("ai.agent_providers.{$agentKey}");
        }

        // 3. Use default provider
        return config('ai.default_provider', 'openai');
    }

    /**
     * Create the AI provider instance based on the provider name.
     */
    protected function createProvider(string $providerName): AIProviderInterface
    {
        $config = config("ai.providers.{$providerName}");

        if (! $config) {
            throw new InvalidArgumentException(
                "AI provider '{$providerName}' is not configured in config/ai.php. ".
                'Available providers: '.implode(', ', $this->getAvailableProviders())
            );
        }

        // Check if API key is set
        if (empty($config['api_key'])) {
            throw new InvalidArgumentException(
                "AI provider '{$providerName}' is configured but missing API key. ".
                'Please set the API key in your .env file. '.
                'Current default provider: '.config('ai.default_provider')
            );
        }

        return match ($providerName) {
            'openai' => $this->createOpenAI($config),
            'anthropic' => $this->createAnthropic($config),
            'deepseek' => $this->createDeepseek($config),
            'gemini' => $this->createGemini($config),
            default => throw new InvalidArgumentException("Unsupported AI provider: {$providerName}"),
        };
    }

    /**
     * Create OpenAI provider instance.
     */
    protected function createOpenAI(array $config): OpenAI
    {
        return new OpenAI(
            key: $config['api_key'],
            model: $config['model'],
        );
    }

    /**
     * Create Anthropic provider instance.
     */
    protected function createAnthropic(array $config): Anthropic
    {
        return new Anthropic(
            key: $config['api_key'],
            model: $config['model'],
            version: $config['version'] ?? '2023-06-01',
            max_tokens: $config['max_tokens'] ?? 8192,
        );
    }

    /**
     * Create DeepSeek provider instance.
     */
    protected function createDeepseek(array $config): Deepseek
    {
        return new Deepseek(
            key: $config['api_key'],
            model: $config['model'],
        );
    }

    /**
     * Create Gemini provider instance.
     */
    protected function createGemini(array $config): Gemini
    {
        return new Gemini(
            key: $config['api_key'],
            model: $config['model'],
        );
    }

    /**
     * Get available provider names.
     */
    public function getAvailableProviders(): array
    {
        return array_keys(config('ai.providers', []));
    }

    /**
     * Check if a provider is configured and has an API key.
     */
    public function isProviderConfigured(string $providerName): bool
    {
        $config = config("ai.providers.{$providerName}");

        return $config && ! empty($config['api_key']);
    }
}
