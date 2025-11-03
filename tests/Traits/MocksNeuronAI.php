<?php

namespace Tests\Traits;

use Mockery;
use NeuronAI\Agent;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\OpenAI\OpenAI;

trait MocksNeuronAI
{
    protected $mockAgent;

    /**
     * Mock the NeuronAI OpenAI provider to prevent actual API calls
     */
    protected function mockNeuronAI(): void
    {
        // Set test API keys
        config([
            'neuron.providers.openai.api_key' => 'sk-test-key',
            'neuron.providers.gemini.api_key' => 'test-gemini-key',
        ]);

        // Mock OpenAI provider class
        $mockOpenAI = Mockery::mock('overload:'.OpenAI::class);
        $mockOpenAI->shouldReceive('__construct')
            ->andReturnSelf();

        // Mock Gemini provider class
        $mockGemini = Mockery::mock('overload:'.Gemini::class);
        $mockGemini->shouldReceive('__construct')
            ->andReturnSelf();

        // Mock Agent class with static make method
        $this->mockAgent = Mockery::mock('overload:'.Agent::class);
        $this->mockAgent->shouldReceive('make')
            ->andReturnSelf();
        $this->mockAgent->shouldReceive('withProvider')
            ->andReturnSelf();
        $this->mockAgent->shouldReceive('withInstructions')
            ->andReturnSelf();
        $this->mockAgent->shouldReceive('chat')
            ->andReturnUsing(function ($message) {
                return $this->getDefaultAIResponse($message);
            });
    }

    /**
     * Get default AI response based on message content
     */
    protected function getDefaultAIResponse(string $message): string
    {
        $message = strtolower($message);

        // Check for various patterns and return appropriate tool selection
        if (str_contains($message, 'show me all') ||
            str_contains($message, 'list') ||
            str_contains($message, 'display') ||
            str_contains($message, 'users')) {
            return json_encode([
                'tools' => ['database'],
                'reasoning' => 'Database query detected',
                'confidence' => 0.95,
            ]);
        }

        if (str_contains($message, 'search') || str_contains($message, 'find information')) {
            return json_encode([
                'tools' => ['search'],
                'reasoning' => 'Search query detected',
                'confidence' => 0.95,
            ]);
        }

        if (str_contains($message, 'tell me about') ||
            str_contains($message, 'hello') ||
            str_contains($message, 'hi there') ||
            str_contains($message, 'good morning') ||
            str_contains($message, 'can you help') ||
            preg_match('/^hey\b/i', $message)) {
            return json_encode([
                'tools' => ['conversation'],
                'reasoning' => 'General conversation detected',
                'confidence' => 1.0,
            ]);
        }

        // Default to conversation for ambiguous messages
        return json_encode([
            'tools' => ['conversation'],
            'reasoning' => 'Default to conversation',
            'confidence' => 0.7,
        ]);
    }

    /**
     * Mock Gemini with custom response
     */
    protected function mockGeminiResponse(string $response): void
    {
        if ($this->mockAgent) {
            $this->mockAgent->shouldReceive('chat')
                ->andReturn($response);
        }
    }

    /**
     * Mock conversation tool responses
     */
    protected function mockConversationTool(): array
    {
        return [
            'type' => 'conversation',
            'response' => 'This is a test response from the conversation tool.',
            'confidence' => 0.95,
        ];
    }

    /**
     * Mock database query tool responses
     */
    protected function mockDatabaseTool(): array
    {
        return [
            'type' => 'database',
            'response' => 'Database query executed successfully.',
            'data' => [
                'count' => 10,
                'records' => [],
            ],
        ];
    }

    /**
     * Mock search engine tool responses
     */
    protected function mockSearchTool(): array
    {
        return [
            'type' => 'search',
            'response' => 'Search completed.',
            'results' => [
                ['title' => 'Result 1', 'snippet' => 'Test result'],
                ['title' => 'Result 2', 'snippet' => 'Another result'],
            ],
        ];
    }
}
