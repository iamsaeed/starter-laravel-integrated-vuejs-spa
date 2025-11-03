<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            // workspace removed
            'user_id' => User::factory(),
            'payment_provider' => 'stripe',
            'provider_payment_method_id' => 'pm_'.Str::random(24),
            'type' => 'card',
            'last4' => '4242',
            'brand' => 'visa',
            'exp_month' => 12,
            'exp_year' => now()->addYears(2)->year,
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'exp_month' => 1,
            'exp_year' => now()->subYear()->year,
        ]);
    }
}
