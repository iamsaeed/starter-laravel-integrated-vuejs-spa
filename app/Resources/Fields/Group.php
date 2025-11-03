<?php

namespace App\Resources\Fields;

class Group
{
    public ?string $label = null;

    public string $cols = 'col-span-12';

    public string $gap = 'gap-4';

    public array $fields = [];

    // Conditional visibility properties
    protected ?array $dependsOn = null;

    protected $showWhen = null;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public static function make(array $fields): static
    {
        return new static($fields);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function cols(string $cols): static
    {
        $this->cols = $cols;

        return $this;
    }

    public function gap(string $gap): static
    {
        $this->gap = $gap;

        return $this;
    }

    /**
     * Show group when condition is met
     */
    public function dependsOn(string $attribute, mixed $value, string $operator = '='): static
    {
        $this->dependsOn = [
            'attribute' => $attribute,
            'value' => $value,
            'operator' => $operator,
        ];

        return $this;
    }

    /**
     * Show group when callback returns true
     */
    public function showWhen(\Closure|array $callback): static
    {
        $this->showWhen = $callback;

        return $this;
    }

    /**
     * Check if group should be visible
     */
    public function isVisible(array $formData): bool
    {
        if ($this->dependsOn !== null) {
            $actualValue = $formData[$this->dependsOn['attribute']] ?? null;
            $expectedValue = $this->dependsOn['value'];
            $operator = $this->dependsOn['operator'];

            return match ($operator) {
                '=' => $actualValue == $expectedValue,
                '!=' => $actualValue != $expectedValue,
                '>' => $actualValue > $expectedValue,
                '>=' => $actualValue >= $expectedValue,
                '<' => $actualValue < $expectedValue,
                '<=' => $actualValue <= $expectedValue,
                'in' => is_array($expectedValue) && in_array($actualValue, $expectedValue),
                'not_in' => is_array($expectedValue) && ! in_array($actualValue, $expectedValue),
                default => false,
            };
        }

        if ($this->showWhen !== null) {
            if (is_callable($this->showWhen)) {
                return call_user_func($this->showWhen, $formData);
            }
        }

        return true;
    }

    public function toArray(): array
    {
        return [
            'type' => 'group',
            'label' => $this->label,
            'cols' => $this->cols,
            'gap' => $this->gap,
            'dependsOn' => $this->dependsOn,
            'fields' => array_map(fn ($field) => $field->toArray(), $this->fields),
        ];
    }
}
