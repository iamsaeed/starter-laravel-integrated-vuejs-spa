<?php

namespace Database\Factories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentRetry>
 */
class PaymentRetryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'subscription_id' => Subscription::factory(),
            // workspace removed
            'payment_provider' => fake()->randomElement(['stripe', 'razorpay']),
            'provider_payment_intent_id' => 'pi_'.Str::random(24),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'currency' => 'USD',
            'attempt_number' => 1,
            'max_attempts' => 3,
            'status' => 'pending',
            'next_retry_at' => now()->addDays(3),
            'last_error' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'next_retry_at' => now()->addDays(3),
        ]);
    }

    public function abandoned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'abandoned',
            'attempt_number' => 3,
        ]);
    }
}
