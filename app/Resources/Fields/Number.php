<?php

namespace App\Resources\Fields;

class Number extends Field
{
    protected function fieldType(): string
    {
        return 'number';
    }

    public function min(int|float $min): static
    {
        return $this->meta(['min' => $min]);
    }

    public function max(int|float $max): static
    {
        return $this->meta(['max' => $max]);
    }

    public function step(int|float $step): static
    {
        return $this->meta(['step' => $step]);
    }
}
