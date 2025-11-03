<?php

namespace App\Resources\Fields;

class BelongsToMany extends Field
{
    protected string $relatedResource;

    protected string $titleAttribute = 'name';

    public function __construct(string $label, ?string $attribute = null)
    {
        parent::__construct($label, $attribute);
    }

    protected function fieldType(): string
    {
        return 'belongs-to-many';
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

    public function showOnIndex(bool $show = true): static
    {
        $this->showOnIndex = $show;

        return $this;
    }

    public function transformValue(mixed $value, $model): mixed
    {
        $relationName = $this->attribute;

        if ($model->relationLoaded($relationName)) {
            $related = $model->$relationName;

            return $related->map(fn ($item) => [
                'id' => $item->id,
                'display' => $item->{$this->titleAttribute} ?? $item->id,
            ])->toArray();
        }

        return [];
    }
}
