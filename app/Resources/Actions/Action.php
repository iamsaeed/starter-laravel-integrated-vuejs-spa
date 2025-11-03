<?php

namespace App\Resources\Actions;

use Illuminate\Database\Eloquent\Collection;

abstract class Action
{
    public string $key;

    public string $label;

    public string $type = 'default'; // default, danger, success

    public bool $confirmable = false;

    public string $confirmMessage = 'Are you sure you want to perform this action?';

    protected array $meta = [];

    public function __construct(?string $label = null, ?string $key = null)
    {
        $this->label = $label ?? 'Action';
        $this->key = $key ?? str($this->label)->snake()->toString();
    }

    public static function make(?string $label = null, ?string $key = null): static
    {
        return new static($label, $key);
    }

    public function confirmable(?string $message = null): static
    {
        $this->confirmable = true;
        if ($message) {
            $this->confirmMessage = $message;
        }

        return $this;
    }

    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function danger(): static
    {
        return $this->type('danger');
    }

    public function success(): static
    {
        return $this->type('success');
    }

    public function meta(array $meta): static
    {
        $this->meta = array_merge($this->meta, $meta);

        return $this;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->actionType(),
            'key' => $this->key,
            'label' => $this->label,
            'style' => $this->type,
            'confirmable' => $this->confirmable,
            'confirmMessage' => $this->confirmMessage,
            'meta' => $this->meta,
        ];
    }

    /**
     * Handle the action.
     */
    abstract public function handle(Collection $models, array $data = []): mixed;

    abstract protected function actionType(): string;
}
