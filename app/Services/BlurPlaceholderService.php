<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Spatie\Image\Drivers\GdDriver;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BlurPlaceholderService
{
    /**
     * Generate and upload a blur placeholder for media.
     *
     * Creates a tiny (20x14px), blurred, low-quality JPEG and uploads it alongside the original.
     *
     * @param  Media  $media  The media model
     * @return string|null URL to the blur placeholder, or null if generation failed
     */
    public function generateAndUploadForMedia(Media $media): ?string
    {
        try {
            // Create temporary files
            $tempSourcePath = sys_get_temp_dir().'/source_'.uniqid().'.'.$media->extension;
            $tempBlurPath = sys_get_temp_dir().'/blur_'.uniqid().'.jpg';

            // Download the original image from S3/Spaces if needed
            $disk = Storage::disk($media->disk);

            // Check if we're dealing with S3/remote storage
            if (in_array($media->disk, ['landlord-s3'])) {
                // Get the full path on S3
                $pathGenerator = app(config('media-library.path_generator'));
                $mediaPath = $pathGenerator->getPath($media).$media->file_name;

                // Download the file from S3 to temp location
                if (! $disk->exists($mediaPath)) {
                    throw new \Exception("File does not exist on S3: {$mediaPath}");
                }

                $fileContents = $disk->get($mediaPath);
                file_put_contents($tempSourcePath, $fileContents);
            } else {
                // For local storage, use the existing path
                $tempSourcePath = $media->getPath();
            }

            // Load and process image using GD driver
            Image::useImageDriver(GdDriver::class)
                ->load($tempSourcePath)
                ->fit(Fit::Contain, 20, 14) // Tiny thumbnail
                ->blur(10) // Heavy blur
                ->quality(60) // Low quality
                ->save($tempBlurPath);

            // Get the path generator to construct the blur placeholder path
            $pathGenerator = app(config('media-library.path_generator'));
            $basePath = $pathGenerator->getPath($media);

            // Construct blur placeholder filename
            $blurFileName = pathinfo($media->file_name, PATHINFO_FILENAME).'_blur.jpg';
            $blurPath = $basePath.$blurFileName;

            // Upload blur placeholder to the same disk as the original media
            $disk->put($blurPath, file_get_contents($tempBlurPath));

            // Clean up temp files
            @unlink($tempBlurPath);
            // Only unlink source if we downloaded it from S3
            if (in_array($media->disk, ['landlord-s3'])) {
                @unlink($tempSourcePath);
            }

            // Return the URL using signed URL for private disks
            if (in_array($media->disk, ['landlord-s3'])) {
                return $disk->temporaryUrl($blurPath, now()->addHours(1));
            }

            return $disk->url($blurPath);
        } catch (\Exception $e) {
            // Log error but don't fail the upload
            \Log::warning('Failed to generate blur placeholder', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Delete blur placeholder for media.
     *
     * @param  Media  $media  The media model
     */
    public function deleteForMedia(Media $media): void
    {
        try {
            $pathGenerator = app(config('media-library.path_generator'));
            $basePath = $pathGenerator->getPath($media);
            $blurFileName = pathinfo($media->file_name, PATHINFO_FILENAME).'_blur.jpg';
            $blurPath = $basePath.$blurFileName;

            Storage::disk($media->disk)->delete($blurPath);
        } catch (\Exception $e) {
            // Silently fail if deletion fails
        }
    }
}
