<?php

namespace App\Resources\Fields;

class Image extends Field
{
    protected string $displayType = 'url'; // 'url', 'svg', 'base64'

    protected ?int $width = null;

    protected ?int $height = null;

    protected bool $rounded = false;

    protected ?string $fallback = null;

    protected function fieldType(): string
    {
        return 'image';
    }

    /**
     * Set the display type (url, svg, base64).
     */
    public function displayType(string $type): static
    {
        $this->displayType = $type;
        $this->meta(['displayType' => $type]);

        return $this;
    }

    /**
     * Display as URL/path to image.
     */
    public function asUrl(): static
    {
        return $this->displayType('url');
    }

    /**
     * Display as inline SVG content.
     */
    public function asSvg(): static
    {
        return $this->displayType('svg');
    }

    /**
     * Display as base64 encoded image.
     */
    public function asBase64(): static
    {
        return $this->displayType('base64');
    }

    /**
     * Set the width of the image.
     */
    public function width(int $width): static
    {
        $this->width = $width;
        $this->meta(['width' => $width]);

        return $this;
    }

    /**
     * Set the height of the image.
     */
    public function height(int $height): static
    {
        $this->height = $height;
        $this->meta(['height' => $height]);

        return $this;
    }

    /**
     * Set both width and height.
     */
    public function size(int $width, int $height): static
    {
        return $this->width($width)->height($height);
    }

    /**
     * Make the image rounded (circular).
     */
    public function rounded(bool $rounded = true): static
    {
        $this->rounded = $rounded;
        $this->meta(['rounded' => $rounded]);

        return $this;
    }

    /**
     * Set a fallback image URL if the image is not available.
     */
    public function fallback(string $url): static
    {
        $this->fallback = $url;
        $this->meta(['fallback' => $url]);

        return $this;
    }

    /**
     * Set alt text for the image.
     */
    public function alt(string $alt): static
    {
        return $this->meta(['alt' => $alt]);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'displayType' => $this->displayType,
            'width' => $this->width,
            'height' => $this->height,
            'rounded' => $this->rounded,
            'fallback' => $this->fallback,
        ]);
    }
}
