<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(
        private TaskService $taskService,
        private CommentService $commentService
    ) {}

    /**
     * Display a listing of comments for a commentable model
     */
    public function index(Request $request, string $commentableType, int $commentableId): JsonResponse
    {
        // Resolve the model class
        $modelClass = $this->resolveModelClass($commentableType);
        if (! $modelClass) {
            return response()->json([
                'message' => 'Invalid commentable type',
            ], 400);
        }

        $model = $modelClass::findOrFail($commentableId);

        // Check access based on model type
        if (! $this->hasAccess($request->user(), $model)) {
            return response()->json([
                'message' => 'You do not have access to view these comments',
            ], 403);
        }

        // Use CommentService to get comments with cached user data
        $comments = $this->commentService->getComments($model);

        return response()->json([
            'data' => $comments,
        ]);
    }

    /**
     * Store a new comment
     */
    public function store(Request $request, string $commentableType, int $commentableId): JsonResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // Resolve the model class
        $modelClass = $this->resolveModelClass($commentableType);
        if (! $modelClass) {
            return response()->json([
                'message' => 'Invalid commentable type',
            ], 400);
        }

        $model = $modelClass::findOrFail($commentableId);

        // Check access based on model type
        if (! $this->hasAccess($request->user(), $model)) {
            return response()->json([
                'message' => 'You do not have access to comment on this item',
            ], 403);
        }

        // Special handling for Task comments to send notifications
        if ($commentableType === 'task' && method_exists($model, 'id')) {
            $comment = $this->taskService->addComment(
                $model,
                $validated['comment'],
                $request->user(),
                $validated['parent_id'] ?? null
            );
        } else {
            $comment = $this->commentService->createComment(
                $model,
                $validated['comment'],
                $request->user()->id,
                $validated['parent_id'] ?? null
            );
        }

        return response()->json([
            'message' => 'Comment added successfully',
            'data' => $comment,
        ], 201);
    }

    /**
     * Update the specified comment
     */
    public function update(Request $request, Comment $comment): JsonResponse
    {
        // Simple authorization - users can only edit their own comments
        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You can only edit your own comments',
            ], 403);
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:5000',
        ]);

        $comment = $this->commentService->updateComment($comment, $validated['comment']);

        return response()->json([
            'message' => 'Comment updated successfully',
            'data' => $comment,
        ]);
    }

    /**
     * Remove the specified comment
     */
    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        // Simple authorization - users can only delete their own comments
        if ($comment->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'You can only delete your own comments',
            ], 403);
        }

        $comment->delete();

        return response()->json([], 204);
    }

    /**
     * Resolve the model class from the commentable type
     */
    private function resolveModelClass(string $commentableType): ?string
    {
        $models = [
            // Add commentable models here as needed
        ];

        return $models[$commentableType] ?? null;
    }

    /**
     * Check if user has access to the model
     */
    private function hasAccess($user, $model): bool
    {
        // Since we removed project/task management, allow all authenticated users
        return true;
    }

    /**
     * Check if user can manage the model
     */
    private function canManage($user, $model): bool
    {
        // Since we removed project/task management, allow all authenticated users
        return true;
    }
}
