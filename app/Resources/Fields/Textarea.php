<?php

namespace App\Resources\Fields;

class Textarea extends Field
{
    protected function fieldType(): string
    {
        return 'textarea';
    }

    public function rows(int $rows): static
    {
        return $this->meta(['rows' => $rows]);
    }

    public function placeholder(string $placeholder): static
    {
        return $this->meta(['placeholder' => $placeholder]);
    }

    public function maxLength(int $length): static
    {
        return $this->meta(['maxLength' => $length]);
    }
}
