<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CommentService
{
    /**
     * Get comments for a commentable model with user data from cache
     */
    public function getComments(Model $model): Collection
    {
        // Get root comments with replies (without user eager loading)
        $comments = $model->comments()
            ->root()
            ->with(['replies']) // Load replies without user
            ->latest()
            ->get();

        // Collect all user IDs (from comments and replies)
        $userIds = $comments->pluck('user_id')
            ->merge($comments->flatMap(function ($comment) {
                return $comment->replies->pluck('user_id');
            }))
            ->unique()
            ->toArray();

        if (empty($userIds)) {
            return $comments;
        }

        // Fetch user data directly from users table
        $users = \App\Models\User::whereIn('id', $userIds)
            ->get()
            ->keyBy('id');

        // Attach user data to comments and replies
        return $comments->map(function ($comment) use ($users) {
            $this->attachUserData($comment, $users);

            // Attach user data to replies
            if ($comment->replies) {
                $comment->replies = $comment->replies->map(function ($reply) use ($users) {
                    $this->attachUserData($reply, $users);

                    return $reply;
                });
            }

            return $comment;
        });
    }

    /**
     * Create a comment with user data attached
     */
    public function createComment(Model $model, string $commentText, int $userId, ?int $parentId = null): Comment
    {
        $comment = $model->comments()->create([
            'user_id' => $userId,
            'comment' => $commentText,
            'parent_id' => $parentId,
        ]);

        // Fetch and attach user data
        $user = \App\Models\User::find($userId);

        if ($user) {
            $this->attachUserData($comment, collect([$userId => $user]));
        }

        return $comment;
    }

    /**
     * Update a comment
     */
    public function updateComment(Comment $comment, string $commentText): Comment
    {
        $comment->update(['comment' => $commentText]);

        // Fetch and attach user data
        $user = \App\Models\User::find($comment->user_id);

        if ($user) {
            $this->attachUserData($comment, collect([$comment->user_id => $user]));
        }

        return $comment;
    }

    /**
     * Delete a comment
     */
    public function deleteComment(Comment $comment): bool
    {
        return $comment->delete();
    }

    /**
     * Attach user data to a comment
     */
    private function attachUserData(Comment $comment, Collection $users): void
    {
        $user = $users->get($comment->user_id);

        if ($user) {
            $comment->user = (object) [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ];
        }
    }
}
