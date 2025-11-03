<?php

namespace App\Resources\Fields;

class Password extends Field
{
    protected bool $requiredOnCreate = true;

    protected bool $requiredOnUpdate = false;

    protected function fieldType(): string
    {
        return 'password';
    }

    public static function make(string $label, ?string $attribute = null): static
    {
        $instance = parent::make($label, $attribute);

        return $instance;
    }

    public function requiredOnCreate(bool $required = true): static
    {
        $this->requiredOnCreate = $required;

        return $this;
    }

    public function requiredOnUpdate(bool $required = true): static
    {
        $this->requiredOnUpdate = $required;

        return $this;
    }

    public function creationRules(string|array $rules): static
    {
        return $this->meta(['creationRules' => $rules]);
    }

    public function updateRules(string|array $rules): static
    {
        return $this->meta(['updateRules' => $rules]);
    }

    public function creationPlaceholder(string $placeholder): static
    {
        return $this->meta(['creationPlaceholder' => $placeholder]);
    }

    public function updatePlaceholder(string $placeholder): static
    {
        return $this->meta(['updatePlaceholder' => $placeholder]);
    }

    public function transformValue(mixed $value, $model): mixed
    {
        // Never return password value
        return null;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        // Add context-specific required flag
        $array['requiredOnCreate'] = $this->requiredOnCreate;
        $array['requiredOnUpdate'] = $this->requiredOnUpdate;

        return $array;
    }
}
