<?php

namespace App\Core\Resources\Fields;

class BelongsTo extends Field
{
    protected string $relatedResource;

    protected string $titleAttribute = 'name';

    protected bool $isSearchable = true;

    protected ?string $customRelationName = null;

    protected function fieldType(): string
    {
        return 'belongs-to';
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

    public function searchable(bool $searchable = true): static
    {
        $this->isSearchable = $searchable;

        return $this->meta(['searchable' => $searchable]);
    }

    public function displayUsing(callable $callback): static
    {
        return $this->meta(['displayCallback' => $callback]);
    }

    public function relation(string $relationName): static
    {
        $this->customRelationName = $relationName;

        return $this;
    }

    public function relationName(): string
    {
        if ($this->customRelationName) {
            return $this->customRelationName;
        }

        // If attribute ends with _id, remove it to get the relation name
        return str($this->attribute)->replace('_id', '')->toString();
    }

    public function transformValue(mixed $value, $model): mixed
    {
        $relationName = $this->relationName();

        // Check if relation exists and is loaded
        if (method_exists($model, $relationName) && $model->relationLoaded($relationName)) {
            $related = $model->$relationName;
            if ($related) {
                return [
                    'id' => $related->id,
                    'display' => $related->{$this->titleAttribute} ?? $related->id,
                ];
            }
        }

        return $value;
    }
}
