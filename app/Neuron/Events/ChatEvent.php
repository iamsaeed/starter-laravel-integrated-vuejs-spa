<?php

namespace App\Neuron\Events;

use NeuronAI\Workflow\Event;

/**
 * Chat event implementation for workflow processing
 */
class ChatEvent implements Event
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Get a specific value from the event data
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Set a value in the event data
     */
    public function set(string $key, $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Check if a key exists in the event data
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Get all event data
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Magic getter for accessing data as properties
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Magic setter for setting data as properties
     */
    public function __set(string $name, $value): void
    {
        $this->set($name, $value);
    }
}
