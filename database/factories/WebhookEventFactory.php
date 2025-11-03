<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebhookEvent>
 */
class WebhookEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'provider' => fake()->randomElement(['stripe', 'razorpay']),
            'provider_event_id' => 'evt_'.Str::random(24),
            'type' => 'invoice.paid',
            'payload' => ['test' => 'data'],
            'processed_at' => null,
            'processing_started_at' => null,
            'status' => 'pending',
            'error_message' => null,
            'retry_count' => 0,
        ];
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => 'Test error',
        ]);
    }
}
