<?php

namespace App\Resources\Fields;

class Section
{
    public string $title;

    public ?string $description = null;

    public ?string $icon = null;

    public bool $collapsible = false;

    public bool $collapsed = false;

    public string $cols = 'col-span-12';

    public string $gap = 'gap-4';

    public ?string $containerClasses = null;

    public array $fields = [];

    // Conditional visibility properties
    protected ?array $dependsOn = null;

    protected $showWhen = null;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public static function make(string $title): static
    {
        return new static($title);
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function icon(string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function collapsible(bool $collapsible = true): static
    {
        $this->collapsible = $collapsible;

        return $this;
    }

    public function collapsed(bool $collapsed = true): static
    {
        $this->collapsed = $collapsed;

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

    public function containerClasses(string $classes): static
    {
        $this->containerClasses = $classes;

        return $this;
    }

    public function fields(array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Show section when condition is met
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
     * Show section when callback returns true
     */
    public function showWhen(\Closure|array $callback): static
    {
        $this->showWhen = $callback;

        return $this;
    }

    /**
     * Check if section should be visible
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
            'type' => 'section',
            'title' => $this->title,
            'description' => $this->description,
            'icon' => $this->icon,
            'collapsible' => $this->collapsible,
            'collapsed' => $this->collapsed,
            'cols' => $this->cols,
            'gap' => $this->gap,
            'containerClasses' => $this->containerClasses,
            'dependsOn' => $this->dependsOn,
            'fields' => array_map(fn ($field) => $field->toArray(), $this->fields),
        ];
    }
}
