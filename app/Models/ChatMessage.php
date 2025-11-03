<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    // Connection removed - using default database

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'chat_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The attributes that should be appended.
     *
     * @var array<int, string>
     */
    protected $appends = ['tools_used'];

    /**
     * Get the conversation that owns the message.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    /**
     * Get the tools used from metadata.
     */
    public function getToolsUsedAttribute(): array
    {
        return $this->metadata['tools_used'] ?? [];
    }

    /**
     * Get the execution time from metadata.
     */
    public function getExecutionTimeAttribute(): ?float
    {
        return $this->metadata['execution_time'] ?? null;
    }

    /**
     * Get the tokens used from metadata.
     */
    public function getTokensUsedAttribute(): ?int
    {
        return $this->metadata['tokens_used'] ?? null;
    }

    /**
     * Check if message is from user.
     */
    public function isUserMessage(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if message is from assistant.
     */
    public function isAssistantMessage(): bool
    {
        return $this->role === 'assistant';
    }

    /**
     * Check if message is a system message.
     */
    public function isSystemMessage(): bool
    {
        return $this->role === 'system';
    }
}
