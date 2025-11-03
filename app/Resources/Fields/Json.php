<?php

namespace App\Resources\Fields;

class Json extends Field
{
    protected function fieldType(): string
    {
        return 'json';
    }

    public function placeholder(string $placeholder): static
    {
        return $this->meta(['placeholder' => $placeholder]);
    }

    public function rows(int $rows): static
    {
        return $this->meta(['rows' => $rows]);
    }

    public function expectedType(string $type): static
    {
        if (! in_array($type, ['array', 'object'])) {
            throw new \InvalidArgumentException('Expected type must be either "array" or "object"');
        }

        return $this->meta(['expectedType' => $type]);
    }

    public function showPreview(bool $show = true): static
    {
        return $this->meta(['showPreview' => $show]);
    }

    public function autoFormat(bool $autoFormat = true): static
    {
        return $this->meta(['autoFormat' => $autoFormat]);
    }

    public function showFormatButton(bool $show = true): static
    {
        return $this->meta(['showFormatButton' => $show]);
    }

    public function showValidateButton(bool $show = true): static
    {
        return $this->meta(['showValidateButton' => $show]);
    }

    public function showValidationIcon(bool $show = true): static
    {
        return $this->meta(['showValidationIcon' => $show]);
    }
}
