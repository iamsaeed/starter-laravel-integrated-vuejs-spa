<?php

namespace App\Resources\Filters;

use Illuminate\Database\Eloquent\Builder;

class BelongsToManyFilter extends Filter
{
    protected array $options = [];

    protected string $relationship;

    protected function filterType(): string
    {
        return 'select';
    }

    public function options(array|string|\Closure $options): static
    {
        if ($options instanceof \Closure) {
            $this->options = $options();
        } elseif (is_string($options) && enum_exists($options)) {
            // Support for Enum classes
            $this->options = collect($options::cases())
                ->mapWithKeys(fn ($case) => [$case->value => $case->name])
                ->toArray();
        } else {
            $this->options = $options;
        }

        return $this;
    }

    public function relationship(string $relationship): static
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        // Transform options to array of objects with value and label
        $array['options'] = collect($this->options)->map(function ($label, $value) {
            return [
                'value' => $value,
                'label' => $label,
            ];
        })->values()->toArray();

        return $array;
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        if (! isset($this->relationship)) {
            throw new \Exception('Relationship must be set for BelongsToManyFilter');
        }

        return $query->whereHas($this->relationship, function ($subQuery) use ($value) {
            $subQuery->where($subQuery->getModel()->getTable().'.id', $value);
        });
    }
}
