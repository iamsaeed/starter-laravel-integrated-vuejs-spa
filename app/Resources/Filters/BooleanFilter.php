<?php

namespace App\Resources\Filters;

use Illuminate\Database\Eloquent\Builder;

class BooleanFilter extends Filter
{
    protected ?string $column = null;

    protected function filterType(): string
    {
        return 'boolean';
    }

    public function column(string $column): static
    {
        $this->column = $column;

        return $this;
    }

    public function trueLabel(string $label): static
    {
        return $this->meta(['trueLabel' => $label]);
    }

    public function falseLabel(string $label): static
    {
        return $this->meta(['falseLabel' => $label]);
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        $column = $this->column ?? $this->key;

        return $query->where($column, (bool) $value);
    }
}
