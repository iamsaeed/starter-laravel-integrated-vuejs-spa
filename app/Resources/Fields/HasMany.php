<?php

namespace App\Resources\Fields;

class HasMany extends Field
{
    protected string $relatedResource;

    public function __construct(string $label, ?string $attribute = null)
    {
        parent::__construct($label, $attribute);
    }

    protected function fieldType(): string
    {
        return 'has-many';
    }

    public function resource(string $resourceClass): static
    {
        $this->relatedResource = $resourceClass;

        return $this->meta([
            'resource' => $resourceClass::key(),
            'resourceClass' => $resourceClass,
        ]);
    }

    public function transformValue(mixed $value, $model): mixed
    {
        $relationName = $this->attribute;

        if ($model->relationLoaded($relationName)) {
            $related = $model->$relationName;

            return [
                'count' => $related->count(),
                'items' => $related->toArray(),
            ];
        }

        return ['count' => 0, 'items' => []];
    }
}
