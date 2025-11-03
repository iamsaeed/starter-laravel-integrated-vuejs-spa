<?php

namespace App\Resources\Filters;

use Illuminate\Database\Eloquent\Builder;

class DateRangeFilter extends Filter
{
    protected ?string $column = null;

    protected function filterType(): string
    {
        return 'date-range';
    }

    public function column(string $column): static
    {
        $this->column = $column;

        return $this;
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        $column = $this->column ?? $this->key;

        if (is_array($value)) {
            if (! empty($value['from'])) {
                $query->whereDate($column, '>=', $value['from']);
            }
            if (! empty($value['to'])) {
                $query->whereDate($column, '<=', $value['to']);
            }
        }

        return $query;
    }
}
