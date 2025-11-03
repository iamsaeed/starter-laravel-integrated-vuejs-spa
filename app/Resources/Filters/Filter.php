<?php

namespace App\Resources\Filters;

use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    public string $key;

    public string $label;

    public mixed $default = null;

    protected array $meta = [];

    public function __construct(string $label, ?string $key = null)
    {
        $this->label = $label;
        $this->key = $key ?? str($label)->snake()->toString();
    }

    public static function make(string $label, ?string $key = null): static
    {
        return new static($label, $key);
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    public function meta(array $meta): static
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->filterType(),
            'key' => $this->key,
            'label' => $this->label,
            'default' => $this->default,
            'meta' => $this->meta,
        ];
    }

    /**
     * Apply the filter to the query.
     */
    abstract public function apply(Builder $query, mixed $value): Builder;

    abstract protected function filterType(): string;
}
