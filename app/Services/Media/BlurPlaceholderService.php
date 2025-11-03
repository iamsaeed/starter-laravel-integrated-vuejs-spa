<?php

namespace App\Services\Media;

use Spatie\Image\Drivers\Gd\GdDriver;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BlurPlaceholderService
{
    /**
     * Generate a blur placeholder for an image
     */
    public function generateBlurPlaceholder(Media $media, int $width = 20, int $blur = 5): string
    {
        // Check if media is an image
        if (! str_starts_with($media->mime_type, 'image/')) {
            return '';
        }

        try {
            $sourcePath = $media->getPath();

            // Create a temporary file for the processed image
            $tempPath = sys_get_temp_dir().'/blur_'.uniqid().'.jpg';

            // Use GD driver directly
            $driver = new GdDriver;
            $driver->loadFile($sourcePath)
                ->fit(Fit::Contain, $width, $width)
                ->blur($blur)
                ->quality(50)
                ->save($tempPath);

            // Read the processed image and encode as base64
            $imageData = file_get_contents($tempPath);
            $base64 = base64_encode($imageData);

            // Clean up temp file
            @unlink($tempPath);

            // Return as data URI
            return 'data:image/jpeg;base64,'.$base64;
        } catch (\Exception $e) {
            // Log error and return empty string
            \Log::error('Failed to generate blur placeholder: '.$e->getMessage(), [
                'media_id' => $media->id,
                'path' => $media->getPath(),
            ]);

            return '';
        }
    }

    /**
     * Generate blur placeholder and store as custom property
     *
     * @param  Media  $media  Media model instance
     */
    public function generateAndStore(Media $media): void
    {
        $placeholder = $this->generateBlurPlaceholder($media);

        if ($placeholder) {
            $media->setCustomProperty('blur_placeholder', $placeholder);
            $media->save();
        }
    }

    /**
     * Get blur placeholder from media
     *
     * @param  Media  $media  Media model instance
     * @return string|null Blur placeholder data URI or null
     */
    public function getBlurPlaceholder(Media $media): ?string
    {
        return $media->getCustomProperty('blur_placeholder');
    }
}
