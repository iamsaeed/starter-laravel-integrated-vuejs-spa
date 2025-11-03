<?php

namespace App\Core\Resources\Fields;

class Text extends Field
{
    protected function fieldType(): string
    {
        return 'text';
    }

    public function placeholder(string $placeholder): static
    {
        return $this->meta(['placeholder' => $placeholder]);
    }

    public function maxLength(int $length): static
    {
        return $this->meta(['maxLength' => $length]);
    }

    public function minLength(int $length): static
    {
        return $this->meta(['minLength' => $length]);
    }
}
