<?php

namespace App\Core\Resources\Fields;

abstract class Field
{
    public string $attribute;

    public string $label;

    public mixed $default = null;

    public array|string|null $rules = null;

    public bool $sortable = false;

    public bool $searchable = false;

    public bool $required = false;

    public bool $nullable = false;

    public bool $creatable = false;

    protected string $cols = 'col-span-12';

    protected ?string $containerClasses = null;

    protected array $meta = [];

    // Conditional visibility properties
    protected ?array $dependsOn = null;

    protected $showWhenCallback = null;

    protected $hideWhenCallback = null;

    protected ?array $requiredWhen = null;

    protected ?array $disabledWhen = null;

    // Static property to track evaluation stack for circular dependency detection
    private static array $evaluationStack = [];

    public function __construct(string $label, ?string $attribute = null)
    {
        $this->label = $label;
        $this->attribute = $attribute ?? str($label)->snake()->toString();
    }

    public static function make(string $label, ?string $attribute = null): static
    {
        return new static($label, $attribute);
    }

    public function rules(array|string $rules): static
    {
        $this->rules = $rules;
        if (is_string($rules) && str_contains($rules, 'required')) {
            $this->required = true;
        }
        if (is_string($rules) && str_contains($rules, 'nullable')) {
            $this->nullable = true;
        }

        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }

    public function nullable(bool $nullable = true): static
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function meta(?array $meta = null): static|array
    {
        if ($meta === null) {
            return $this->meta;
        }

        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        return $this->meta(['placeholder' => $placeholder]);
    }

    public function help(string $helpText): static
    {
        return $this->meta(['helpText' => $helpText]);
    }

    public function creatable(bool $creatable = true): static
    {
        $this->creatable = $creatable;

        return $this;
    }

    public function cols(string $cols): static
    {
        $this->cols = $cols;

        return $this;
    }

    public function containerClasses(string $classes): static
    {
        $this->containerClasses = $classes;

        return $this;
    }

    /**
     * Show field when another field has a specific value
     */
    public function dependsOn(string $attribute, mixed $value, string $operator = '='): static
    {
        $this->dependsOn = [
            'attribute' => $attribute,
            'value' => $value,
            'operator' => $operator,
        ];

        return $this->meta(['dependsOn' => $this->dependsOn]);
    }

    /**
     * Show field when ALL conditions are met (AND logic)
     */
    public function dependsOnAll(array $conditions): static
    {
        $this->dependsOn = [
            'type' => 'all',
            'conditions' => $conditions,
        ];

        return $this->meta(['dependsOn' => $this->dependsOn]);
    }

    /**
     * Show field when ANY condition is met (OR logic)
     */
    public function dependsOnAny(array $conditions): static
    {
        $this->dependsOn = [
            'type' => 'any',
            'conditions' => $conditions,
        ];

        return $this->meta(['dependsOn' => $this->dependsOn]);
    }

    /**
     * Show field when callback or structured condition returns true
     */
    public function showWhen(\Closure|array $condition): static
    {
        if (is_callable($condition)) {
            // Callback: backend only
            $this->showWhenCallback = $condition;
            $this->meta['showWhen'] = ['type' => 'callback', 'frontend' => false];
        } else {
            // Structured condition: both backend and frontend
            $this->meta['showWhen'] = $condition;
        }

        return $this;
    }

    /**
     * Hide field when callback or structured condition returns true
     */
    public function hideWhen(\Closure|array $condition): static
    {
        if (is_callable($condition)) {
            // Callback: backend only
            $this->hideWhenCallback = $condition;
            $this->meta['hideWhen'] = ['type' => 'callback', 'frontend' => false];
        } else {
            // Structured condition: both backend and frontend
            $this->meta['hideWhen'] = $condition;
        }

        return $this;
    }

    /**
     * Make field required when condition is met
     */
    public function requiredWhen(string $attribute, mixed $value, string $operator = '='): static
    {
        $this->requiredWhen = [
            'attribute' => $attribute,
            'value' => $value,
            'operator' => $operator,
        ];

        return $this->meta(['requiredWhen' => $this->requiredWhen]);
    }

    /**
     * Disable field when condition is met
     */
    public function disabledWhen(string $attribute, mixed $value, string $operator = '='): static
    {
        $this->disabledWhen = [
            'attribute' => $attribute,
            'value' => $value,
            'operator' => $operator,
        ];

        return $this->meta(['disabledWhen' => $this->disabledWhen]);
    }

    /**
     * Evaluate if field should be visible based on form data
     */
    public function isVisible(array $formData): bool
    {
        // Detect circular dependency
        if (in_array($this->attribute, self::$evaluationStack)) {
            throw new \RuntimeException(
                'Circular dependency detected: '.implode(' -> ', self::$evaluationStack)." -> {$this->attribute}"
            );
        }

        self::$evaluationStack[] = $this->attribute;

        try {
            $visible = $this->evaluateVisibility($formData);
        } finally {
            array_pop(self::$evaluationStack);
        }

        return $visible;
    }

    /**
     * Internal visibility evaluation
     */
    protected function evaluateVisibility(array $formData): bool
    {
        // Check dependsOn
        if ($this->dependsOn !== null) {
            if (! $this->evaluateDependsOn($formData)) {
                return false;
            }
        }

        // Check showWhen callback
        if ($this->showWhenCallback !== null) {
            if (! call_user_func($this->showWhenCallback, $formData)) {
                return false;
            }
        }

        // Check showWhen structured condition
        if (isset($this->meta['showWhen']) && is_array($this->meta['showWhen']) && ! isset($this->meta['showWhen']['type'])) {
            if (! $this->evaluateStructuredCondition($this->meta['showWhen'], $formData)) {
                return false;
            }
        }

        // Check hideWhen callback
        if ($this->hideWhenCallback !== null) {
            if (call_user_func($this->hideWhenCallback, $formData)) {
                return false;
            }
        }

        // Check hideWhen structured condition
        if (isset($this->meta['hideWhen']) && is_array($this->meta['hideWhen']) && ! isset($this->meta['hideWhen']['type'])) {
            if ($this->evaluateStructuredCondition($this->meta['hideWhen'], $formData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate if field should be required based on form data
     */
    public function isRequired(array $formData): bool
    {
        if ($this->requiredWhen !== null) {
            return $this->evaluateCondition(
                $formData,
                $this->requiredWhen['attribute'],
                $this->requiredWhen['value'],
                $this->requiredWhen['operator']
            );
        }

        return $this->required;
    }

    /**
     * Evaluate if field should be disabled based on form data
     */
    public function isDisabled(array $formData): bool
    {
        if ($this->disabledWhen !== null) {
            return $this->evaluateCondition(
                $formData,
                $this->disabledWhen['attribute'],
                $this->disabledWhen['value'],
                $this->disabledWhen['operator']
            );
        }

        return false;
    }

    /**
     * Evaluate dependsOn conditions
     */
    protected function evaluateDependsOn(array $formData): bool
    {
        if (isset($this->dependsOn['type'])) {
            // Multiple conditions
            $conditions = $this->dependsOn['conditions'];

            if ($this->dependsOn['type'] === 'all') {
                // AND logic - all must be true
                foreach ($conditions as $condition) {
                    if (! $this->evaluateCondition($formData, ...$condition)) {
                        return false;
                    }
                }

                return true;
            } else {
                // OR logic - at least one must be true
                foreach ($conditions as $condition) {
                    if ($this->evaluateCondition($formData, ...$condition)) {
                        return true;
                    }
                }

                return false;
            }
        } else {
            // Single condition
            return $this->evaluateCondition(
                $formData,
                $this->dependsOn['attribute'],
                $this->dependsOn['value'],
                $this->dependsOn['operator']
            );
        }
    }

    /**
     * Evaluate a structured condition
     */
    protected function evaluateStructuredCondition(array $condition, array $formData): bool
    {
        return match ($condition['type']) {
            'comparison' => $this->evaluateCondition(
                $formData,
                $condition['field'],
                $condition['value'],
                $condition['operator']
            ),
            'and' => collect($condition['conditions'])
                ->every(fn ($c) => $this->evaluateStructuredCondition($c, $formData)),
            'or' => collect($condition['conditions'])
                ->contains(fn ($c) => $this->evaluateStructuredCondition($c, $formData)),
            default => false,
        };
    }

    /**
     * Evaluate a single condition
     */
    protected function evaluateCondition(
        array $formData,
        string $attribute,
        mixed $expectedValue,
        string $operator = '='
    ): bool {
        // If dependency field doesn't exist, default to hidden
        if (! array_key_exists($attribute, $formData)) {
            return false;
        }

        $actualValue = $formData[$attribute];

        // Handle null values
        if ($actualValue === null) {
            return $expectedValue === null;
        }

        return match ($operator) {
            '=' => $actualValue == $expectedValue,
            '!=' => $actualValue != $expectedValue,
            '>' => $actualValue > $expectedValue,
            '>=' => $actualValue >= $expectedValue,
            '<' => $actualValue < $expectedValue,
            '<=' => $actualValue <= $expectedValue,
            'in' => is_array($expectedValue) && in_array($actualValue, $expectedValue),
            'not_in' => is_array($expectedValue) && ! in_array($actualValue, $expectedValue),
            'contains' => is_array($actualValue) && in_array($expectedValue, $actualValue),
            'not_contains' => is_array($actualValue) && ! in_array($expectedValue, $actualValue),
            'empty' => empty($actualValue),
            'not_empty' => ! empty($actualValue),
            default => false,
        };
    }

    public function toArray(): array
    {
        return [
            'type' => $this->fieldType(),
            'attribute' => $this->attribute,
            'label' => $this->label,
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'required' => $this->required,
            'nullable' => $this->nullable,
            'creatable' => $this->creatable,
            'default' => $this->default,
            'cols' => $this->cols,
            'containerClasses' => $this->containerClasses,
            'meta' => $this->meta,
        ];
    }

    /**
     * Transform value for display.
     */
    public function transformValue(mixed $value, $model): mixed
    {
        return $value;
    }

    abstract protected function fieldType(): string;
}
