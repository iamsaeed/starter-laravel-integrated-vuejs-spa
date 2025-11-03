<?php

namespace App\Services;

use App\Models\ChatConversation;
use Illuminate\Support\Collection;

class ChatService
{
    /**
     * Export a conversation in the specified format
     */
    public function exportConversation(ChatConversation $conversation, string $format = 'text'): string
    {
        return match ($format) {
            'json' => $this->exportAsJson($conversation),
            'pdf' => $this->exportAsPdf($conversation),
            default => $this->exportAsText($conversation),
        };
    }

    /**
     * Export conversation as plain text
     */
    private function exportAsText(ChatConversation $conversation): string
    {
        $output = "Conversation: {$conversation->title}\n";
        $output .= "Date: {$conversation->created_at->format('Y-m-d H:i:s')}\n";
        $output .= str_repeat('-', 50)."\n\n";

        foreach ($conversation->messages as $message) {
            $role = ucfirst($message->role);
            $timestamp = $message->created_at->format('H:i:s');
            $output .= "[{$timestamp}] {$role}:\n{$message->content}\n\n";
        }

        return $output;
    }

    /**
     * Export conversation as JSON
     */
    private function exportAsJson(ChatConversation $conversation): string
    {
        $data = [
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'created_at' => $conversation->created_at->toIso8601String(),
                'updated_at' => $conversation->updated_at->toIso8601String(),
            ],
            'messages' => $conversation->messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'role' => $message->role,
                    'content' => $message->content,
                    'tools_used' => $message->tools_used,
                    'created_at' => $message->created_at->toIso8601String(),
                ];
            })->toArray(),
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Export conversation as PDF (placeholder - implement with PDF library)
     */
    private function exportAsPdf(ChatConversation $conversation): string
    {
        // This would require a PDF library like DomPDF or TCPDF
        // For now, return text format
        return $this->exportAsText($conversation);
    }

    /**
     * Get conversation statistics
     */
    public function getConversationStats(ChatConversation $conversation): array
    {
        $messages = $conversation->messages;

        return [
            'total_messages' => $messages->count(),
            'user_messages' => $messages->where('role', 'user')->count(),
            'assistant_messages' => $messages->where('role', 'assistant')->count(),
            'total_tokens' => $messages->sum('metadata.tokens_used'),
            'average_response_time' => $messages->avg('metadata.execution_time'),
            'tools_used' => $this->getToolsUsedStats($messages),
        ];
    }

    /**
     * Get tools usage statistics
     */
    private function getToolsUsedStats(Collection $messages): array
    {
        $toolsUsage = [];

        foreach ($messages as $message) {
            $tools = $message->tools_used;
            foreach ($tools as $tool) {
                if (! isset($toolsUsage[$tool])) {
                    $toolsUsage[$tool] = 0;
                }
                $toolsUsage[$tool]++;
            }
        }

        return $toolsUsage;
    }

    /**
     * Clean old conversations
     */
    public function cleanOldConversations(int $daysOld = 30): int
    {
        return ChatConversation::where('updated_at', '<', now()->subDays($daysOld))
            ->whereDoesntHave('messages', function ($query) {
                $query->where('created_at', '>=', now()->subDays(7));
            })
            ->delete();
    }

    /**
     * Get user's conversation summary
     */
    public function getUserConversationSummary(int $userId): array
    {
        $conversations = ChatConversation::where('user_id', $userId)
            ->withCount('messages')
            ->get();

        return [
            'total_conversations' => $conversations->count(),
            'total_messages' => $conversations->sum('messages_count'),
            'active_conversations' => $conversations->where('updated_at', '>=', now()->subDays(7))->count(),
            'oldest_conversation' => $conversations->min('created_at'),
            'newest_conversation' => $conversations->max('created_at'),
        ];
    }
}
