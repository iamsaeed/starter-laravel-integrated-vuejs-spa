<?php

namespace App\Neuron\Nodes;

use NeuronAI\Workflow\Event;
use NeuronAI\Workflow\Node;

/**
 * Initial node that receives and validates the user's chat message
 */
class ChatInputNode extends Node
{
    /**
     * Handle the incoming chat message event
     */
    public function handle(Event $event): Event
    {
        $message = $event->get('message');
        $context = $event->get('context', []);

        // Validate the message
        if (empty($message)) {
            return new Event([
                'error' => 'Message cannot be empty',
                'status' => 'error',
            ]);
        }

        // Clean and prepare the message
        $cleanedMessage = $this->cleanMessage($message);

        // Return the processed event with cleaned data
        return new Event([
            'message' => $cleanedMessage,
            'original_message' => $message,
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
            'status' => 'processed',
        ]);
    }

    /**
     * Clean and sanitize the message
     */
    private function cleanMessage(string $message): string
    {
        // Remove excess whitespace
        $message = trim($message);

        // Remove any potentially harmful content
        $message = strip_tags($message);

        // Normalize whitespace
        $message = preg_replace('/\s+/', ' ', $message);

        return $message;
    }
}
