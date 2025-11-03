<?php

namespace App\Neuron\Tools;

use Illuminate\Support\Facades\Log;
use NeuronAI\Agent;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Providers\OpenAI\OpenAI;

/**
 * General conversation tool for handling chat interactions
 */
class ConversationTool implements Tool
{
    private ?Agent $chatAgent = null;

    /**
     * Get a description of what this tool does
     */
    public function getDescription(): string
    {
        return 'General conversation and questions about the application, greetings, help requests, and general inquiries';
    }

    /**
     * Execute the conversation tool
     */
    public function execute(string $message, array $context): array
    {
        try {
            $this->initializeChatAgent($context);

            // Add context to the conversation
            $contextualMessage = $this->addContextToMessage($message, $context);

            // Get response from AI
            $userMessage = UserMessage::make($contextualMessage);
            $response = $this->chatAgent->chat($userMessage);

            return [
                'type' => 'conversation',
                'response' => $response->getContent(),
                'confidence' => 0.95,
            ];

        } catch (\Exception $e) {
            Log::error('ConversationTool error', [
                'error' => $e->getMessage(),
                'message' => $message,
            ]);

            // Return a fallback response
            return $this->getFallbackResponse($message);
        }
    }

    /**
     * Initialize the chat agent
     */
    private function initializeChatAgent(array $context): void
    {
        if (! $this->chatAgent) {
            $systemPrompt = $this->getSystemPrompt($context);

            $provider = new OpenAI(
                config('neuron.providers.openai.api_key'),
                config('neuron.providers.openai.model', 'gpt-4o-mini')
            );

            $this->chatAgent = Agent::make()
                ->withProvider($provider)
                ->withInstructions($systemPrompt);
        }
    }

    /**
     * Add context to the message
     */
    private function addContextToMessage(string $message, array $context): string
    {
        $contextualMessage = $message;

        // Add message history if available
        if (isset($context['message_history']) && ! empty($context['message_history'])) {
            $history = "Previous conversation:\n";
            foreach (array_slice($context['message_history'], -5) as $msg) {
                $history .= sprintf("%s: %s\n",
                    ucfirst($msg['role']),
                    substr($msg['content'], 0, 200)
                );
            }
            $contextualMessage = $history."\nCurrent message: ".$message;
        }

        return $contextualMessage;
    }

    /**
     * Get the system prompt based on context
     */
    private function getSystemPrompt(array $context): string
    {
        $userName = $context['user']->name ?? 'User';

        return <<<PROMPT
        You are a helpful AI assistant for a business application.
        You are currently assisting {$userName}.

        Your capabilities include:
        - Answering general questions
        - Providing guidance on using the application
        - Helping with expense tracking
        - Offering general business advice

        Be friendly, professional, and concise in your responses.
        If asked about specific data or actions, guide the user on how to perform those actions.
        Always maintain a helpful and supportive tone.
        PROMPT;
    }

    /**
     * Get a fallback response when AI is unavailable
     */
    private function getFallbackResponse(string $message): array
    {
        $responses = [
            'greeting' => "Hello! I'm here to help you with your tasks and questions. What can I assist you with today?",
            'help' => "I can help you with:\n• Managing expenses\n• General questions about the application\n• Business advice and best practices\n\nWhat would you like to know more about?",
            'error' => "I apologize, but I'm having trouble processing your request at the moment. Please try again in a few moments.",
            'default' => "I understand you need help. Could you please provide more details about what you're looking for?",
        ];

        // Simple keyword matching for fallback
        $lowerMessage = strtolower($message);

        if (preg_match('/\b(hello|hi|hey|greet)\b/', $lowerMessage)) {
            $response = $responses['greeting'];
        } elseif (preg_match('/\b(help|how|what|guide)\b/', $lowerMessage)) {
            $response = $responses['help'];
        } elseif (preg_match('/\b(error|problem|issue|wrong)\b/', $lowerMessage)) {
            $response = $responses['error'];
        } else {
            $response = $responses['default'];
        }

        return [
            'type' => 'conversation',
            'response' => $response,
            'confidence' => 0.5,
            'fallback' => true,
        ];
    }
}
