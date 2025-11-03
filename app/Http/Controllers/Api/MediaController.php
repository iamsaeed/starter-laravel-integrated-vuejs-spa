<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Media\BlurPlaceholderService;
use App\Services\Media\SecureMediaUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function __construct(
        protected BlurPlaceholderService $blurPlaceholderService,
        protected SecureMediaUrlService $secureMediaUrlService
    ) {}

    /**
     * Upload a single file.
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'collection' => 'required|string',
        ]);

        $rawModelType = $request->input('model_type');
        $modelId = $request->input('model_id');
        $collection = $request->input('collection');

        // Normalize the model type - handle both single and double backslashes
        // Replace any sequence of backslashes with a single backslash
        $modelType = preg_replace('/\\\\+/', '\\', $rawModelType);

        // Validate that the model class exists
        if (! class_exists($modelType)) {
            return response()->json([
                'message' => 'Invalid model type',
                'error' => "Class {$modelType} not found. Received: {$rawModelType}",
            ], 422);
        }

        // Get the model instance
        $model = $modelType::findOrFail($modelId);

        if (! method_exists($model, 'addMedia')) {
            return response()->json([
                'message' => 'Model does not support media uploads',
            ], 422);
        }

        // Add the file to the model
        $media = $model->addMediaFromRequest('file')
            ->toMediaCollection($collection);

        // Generate blur placeholder for images
        if (str_starts_with($media->mime_type, 'image/')) {
            $this->blurPlaceholderService->generateAndStore($media);
        }

        return response()->json([
            'message' => 'File uploaded successfully',
            'data' => [
                'id' => $media->id,
                'name' => $media->file_name,
                'url' => $this->secureMediaUrlService->getTemporaryUrl($media),
                'thumbnail' => $media->hasGeneratedConversion('thumb')
                    ? $this->secureMediaUrlService->getTemporaryConversionUrl($media, 'thumb')
                    : $this->secureMediaUrlService->getTemporaryUrl($media),
                'blur_placeholder' => $this->blurPlaceholderService->getBlurPlaceholder($media),
                'size' => $media->size,
                'mime_type' => $media->mime_type,
            ],
        ], 201);
    }

    /**
     * Upload multiple files.
     */
    public function uploadMultiple(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240',
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'collection' => 'required|string',
        ]);

        $rawModelType = $request->input('model_type');
        $modelId = $request->input('model_id');
        $collection = $request->input('collection');

        // Normalize the model type - handle both single and double backslashes
        // Replace any sequence of backslashes with a single backslash
        $modelType = preg_replace('/\\\\+/', '\\', $rawModelType);

        // Validate that the model class exists
        if (! class_exists($modelType)) {
            return response()->json([
                'message' => 'Invalid model type',
                'error' => "Class {$modelType} not found. Received: {$rawModelType}",
            ], 422);
        }

        $model = $modelType::findOrFail($modelId);

        if (! method_exists($model, 'addMedia')) {
            return response()->json([
                'message' => 'Model does not support media uploads',
            ], 422);
        }

        $uploadedMedia = [];

        foreach ($request->file('files') as $file) {
            $media = $model->addMedia($file)
                ->toMediaCollection($collection);

            // Generate blur placeholder for images
            if (str_starts_with($media->mime_type, 'image/')) {
                $this->blurPlaceholderService->generateAndStore($media);
            }

            $uploadedMedia[] = [
                'id' => $media->id,
                'name' => $media->file_name,
                'url' => $this->secureMediaUrlService->getTemporaryUrl($media),
                'thumbnail' => $media->hasGeneratedConversion('thumb')
                    ? $this->secureMediaUrlService->getTemporaryConversionUrl($media, 'thumb')
                    : $this->secureMediaUrlService->getTemporaryUrl($media),
                'blur_placeholder' => $this->blurPlaceholderService->getBlurPlaceholder($media),
                'size' => $media->size,
                'mime_type' => $media->mime_type,
            ];
        }

        return response()->json([
            'message' => 'Files uploaded successfully',
            'data' => $uploadedMedia,
        ], 201);
    }

    /**
     * Download a media file.
     */
    public function download(Media $media)
    {
        // Verify user has access to this media's model
        $model = $media->model;

        if (! $model || ! $this->canAccessMedia($model)) {
            abort(403, 'Unauthorized access to media');
        }

        return response()->download($media->getPath(), $media->file_name);
    }

    /**
     * Delete a media file.
     */
    public function destroy(Media $media): JsonResponse
    {
        // Verify user has access to this media's model
        $model = $media->model;

        if (! $model || ! $this->canAccessMedia($model)) {
            abort(403, 'Unauthorized access to media');
        }

        $media->delete();

        return response()->json([
            'message' => 'File deleted successfully',
        ]);
    }

    /**
     * Check if the current user can access the media's parent model.
     */
    protected function canAccessMedia($model): bool
    {
        // Get current user
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Default: allow access (can be customized per model type)
        return true;
    }
}
