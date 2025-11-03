<?php

namespace App\Resources\Fields;

class Media extends Field
{
    protected bool $multiple = false;

    protected string $collection = 'default';

    protected array $acceptedTypes = ['image/*'];

    protected ?int $maxFiles = null;

    protected ?int $maxFileSize = null; // in MB

    protected ?int $previewWidth = null;

    protected ?int $previewHeight = null;

    protected bool $rounded = false;

    protected ?string $disk = null;

    protected bool $editable = false;

    protected array $editorOptions = [];

    protected function fieldType(): string
    {
        return 'media';
    }

    /**
     * Enable single file mode (default).
     */
    public function single(): static
    {
        $this->multiple = false;
        $this->maxFiles = 1;
        $this->meta(['multiple' => false, 'maxFiles' => 1]);

        return $this;
    }

    /**
     * Enable multiple files mode.
     */
    public function multiple(?int $maxFiles = null): static
    {
        $this->multiple = true;
        $this->maxFiles = $maxFiles;
        $this->meta(['multiple' => true, 'maxFiles' => $maxFiles]);

        return $this;
    }

    /**
     * Set the media collection name.
     */
    public function collection(string $collection): static
    {
        $this->collection = $collection;
        $this->meta(['collection' => $collection]);

        return $this;
    }

    /**
     * Set accepted file types.
     */
    public function acceptedTypes(array $types): static
    {
        $this->acceptedTypes = $types;
        $this->meta(['acceptedTypes' => $types]);

        return $this;
    }

    /**
     * Accept only images.
     */
    public function images(): static
    {
        return $this->acceptedTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
    }

    /**
     * Accept only documents.
     */
    public function documents(): static
    {
        return $this->acceptedTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    /**
     * Set maximum file size in MB.
     */
    public function maxFileSize(int $mb): static
    {
        $this->maxFileSize = $mb;
        $this->meta(['maxFileSize' => $mb]);

        return $this;
    }

    /**
     * Set preview dimensions.
     */
    public function previewSize(int $width, int $height): static
    {
        $this->previewWidth = $width;
        $this->previewHeight = $height;
        $this->meta(['previewWidth' => $width, 'previewHeight' => $height]);

        return $this;
    }

    /**
     * Make preview images rounded (circular).
     */
    public function rounded(bool $rounded = true): static
    {
        $this->rounded = $rounded;
        $this->meta(['rounded' => $rounded]);

        return $this;
    }

    /**
     * Set the disk to store media on.
     */
    public function disk(string $disk): static
    {
        $this->disk = $disk;
        $this->meta(['disk' => $disk]);

        return $this;
    }

    /**
     * Enable image editing before upload.
     */
    public function editable(array $options = []): static
    {
        $this->editable = true;
        $this->editorOptions = $options;
        $this->meta([
            'editable' => true,
            'editorOptions' => $options,
        ]);

        return $this;
    }

    /**
     * Transform value to include media URLs.
     */
    public function transformValue(mixed $value, $model): mixed
    {
        // Check if model has the getMedia method (Spatie Media Library trait)
        if (! $model || ! method_exists($model, 'getMedia')) {
            return null;
        }

        try {
            $media = $model->getMedia($this->collection);

            if ($this->multiple) {
                $items = $media->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->file_name,
                        'url' => $item->getUrl(),
                        'thumbnail' => $item->hasGeneratedConversion('thumb') ? $item->getUrl('thumb') : $item->getUrl(),
                        'size' => $item->size,
                        'mime_type' => $item->mime_type,
                    ];
                })->toArray();

                return empty($items) ? null : $items;
            }

            $firstMedia = $media->first();

            if (! $firstMedia) {
                return null;
            }

            return [
                'id' => $firstMedia->id,
                'name' => $firstMedia->file_name,
                'url' => $firstMedia->getUrl(),
                'thumbnail' => $firstMedia->hasGeneratedConversion('thumb') ? $firstMedia->getUrl('thumb') : $firstMedia->getUrl(),
                'size' => $firstMedia->size,
                'mime_type' => $firstMedia->mime_type,
            ];
        } catch (\Exception $e) {
            // Log error but return null to prevent breaking the API
            \Log::error('Media transform error: '.$e->getMessage());

            return null;
        }
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'multiple' => $this->multiple,
            'collection' => $this->collection,
            'acceptedTypes' => $this->acceptedTypes,
            'maxFiles' => $this->maxFiles,
            'maxFileSize' => $this->maxFileSize,
            'previewWidth' => $this->previewWidth,
            'previewHeight' => $this->previewHeight,
            'rounded' => $this->rounded,
            'disk' => $this->disk,
            'editable' => $this->editable,
            'editorOptions' => $this->editorOptions,
        ]);
    }
}
