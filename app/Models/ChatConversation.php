<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChatConversation extends Model
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
    protected $table = 'chat_conversations';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'title',
        'context',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'context' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }

            if (empty($model->title)) {
                $model->title = 'New Chat';
            }
        });
    }

    /**
     * Get the messages for the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    /**
     * Get the user that owns the conversation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the latest message in the conversation.
     */
    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class, 'conversation_id')->latestOfMany();
    }

    /**
     * Update the conversation title based on the first message.
     */
    public function generateTitle(): void
    {
        $firstMessage = $this->messages()->orderBy('created_at')->first();

        if ($firstMessage && $this->title === 'New Chat') {
            // Take first 50 characters of the message as title
            $title = Str::limit($firstMessage->content, 50, '...');

            // Clean up the title
            $title = str_replace(["\n", "\r", "\t"], ' ', $title);
            $title = preg_replace('/\s+/', ' ', $title);

            $this->update(['title' => $title]);
        }
    }

    /**
     * Clear all messages in the conversation.
     */
    public function clearMessages(): void
    {
        $this->messages()->delete();
    }

    /**
     * Get message count for the conversation.
     */
    public function getMessageCountAttribute(): int
    {
        return $this->messages()->count();
    }

    /**
     * Check if conversation belongs to user.
     */
    public function belongsToUser(User $user): bool
    {
        return $this->user_id === $user->id;
    }
}
