<?php

namespace App\Resources\Fields;

class Email extends Field
{
    protected function fieldType(): string
    {
        return 'email';
    }

    public function placeholder(string $placeholder): static
    {
        return $this->meta(['placeholder' => $placeholder]);
    }
}
