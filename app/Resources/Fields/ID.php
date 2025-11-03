<?php

namespace App\Resources\Fields;

class ID extends Field
{
    public function __construct()
    {
        parent::__construct('ID', 'id');
        $this->showOnForm = false;
    }

    public static function make(?string $label = null, ?string $attribute = null): static
    {
        return new static;
    }

    protected function fieldType(): string
    {
        return 'id';
    }
}
