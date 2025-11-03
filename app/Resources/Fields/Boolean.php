<?php

namespace App\Resources\Fields;

class Boolean extends Field
{
    protected function fieldType(): string
    {
        return 'boolean';
    }

    public function trueLabel(string $label): static
    {
        return $this->meta(['trueLabel' => $label]);
    }

    public function falseLabel(string $label): static
    {
        return $this->meta(['falseLabel' => $label]);
    }

    public function toggleable(bool $toggleable = true): static
    {
        return $this->meta(['toggleable' => $toggleable]);
    }

    public function transformValue(mixed $value, $model): mixed
    {
        return (bool) $value;
    }
}
