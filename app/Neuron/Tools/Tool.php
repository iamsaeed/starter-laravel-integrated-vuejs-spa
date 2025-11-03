<?php

namespace App\Neuron\Tools;

/**
 * Base interface for all tools
 */
interface Tool
{
    /**
     * Execute the tool with the given message and context
     *
     * @param  string  $message  The user's message
     * @param  array  $context  Additional context (user, etc.)
     * @return array The tool's response
     */
    public function execute(string $message, array $context): array;

    /**
     * Get a description of what this tool does
     *
     * @return string A brief description of the tool's purpose
     */
    public function getDescription(): string;
}
