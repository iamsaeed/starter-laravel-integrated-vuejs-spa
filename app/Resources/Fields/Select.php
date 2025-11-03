<?php

namespace App\Resources\Fields;

class Select extends Field
{
    protected array $options = [];

    protected bool $multiple = false;

    protected ?string $relatedResource = null;

    protected string $titleAttribute = 'name';

    protected function fieldType(): string
    {
        return 'select';
    }

    public function options(array|string $options): static
    {
        // Support for Enum classes
        if (is_string($options) && enum_exists($options)) {
            $this->options = collect($options::cases())
                ->mapWithKeys(fn ($case) => [$case->value => $case->name])
                ->toArray();
        } else {
            $this->options = $options;
        }

        return $this->meta(['options' => $this->options]);
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this->meta(['multiple' => $multiple]);
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this->meta(['searchable' => $searchable]);
    }

    public function maxSelections(?int $max): static
    {
        return $this->meta(['maxSelections' => $max]);
    }

    public function serverSide(bool $serverSide = true): static
    {
        return $this->meta(['serverSide' => $serverSide]);
    }

    public function resource(string $resourceClass): static
    {
        $this->relatedResource = $resourceClass;

        return $this->meta([
            'resource' => $resourceClass::key(),
            'resourceClass' => $resourceClass,
        ]);
    }

    public function titleAttribute(string $attribute): static
    {
        $this->titleAttribute = $attribute;

        return $this->meta(['titleAttribute' => $attribute]);
    }

    public function displayUsing(callable $callback): static
    {
        return $this->meta(['displayCallback' => $callback]);
    }

    public function toggleable(bool $toggleable = true, ?string $trueValue = null, ?string $falseValue = null): static
    {
        $meta = ['toggleable' => $toggleable];

        if ($toggleable && $trueValue !== null && $falseValue !== null) {
            $meta['toggleTrueValue'] = $trueValue;
            $meta['toggleFalseValue'] = $falseValue;
        }

        return $this->meta($meta);
    }

    public function transformValue(mixed $value, $model): mixed
    {
        // If this is a relationship field (multiple select for relationships)
        if ($this->multiple && $this->relatedResource) {
            $relationName = $this->attribute;

            if ($model->relationLoaded($relationName)) {
                $related = $model->$relationName;

                return $related->map(fn ($item) => $item->id)->toArray();
            }

            return [];
        }

        return parent::transformValue($value, $model);
    }
}
