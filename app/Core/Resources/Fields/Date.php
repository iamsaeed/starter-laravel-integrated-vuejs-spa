<?php

namespace App\Core\Resources\Fields;

class Date extends Field
{
    protected function fieldType(): string
    {
        return 'date';
    }

    public function format(string $format): static
    {
        return $this->meta(['format' => $format]);
    }

    public function min(string $min): static
    {
        return $this->meta(['min' => $min]);
    }

    public function max(string $max): static
    {
        return $this->meta(['max' => $max]);
    }

    public function transformValue(mixed $value, $model): mixed
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }
}
