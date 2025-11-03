<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    private static int $sequence = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate truly unique codes using atomic counter + random
        self::$sequence++;
        $random = bin2hex(random_bytes(2));
        $uniqueCode = str_pad(dechex(self::$sequence), 2, '0', STR_PAD_LEFT).$random;

        $code = strtoupper(substr($uniqueCode, 0, 2));
        $code3 = strtoupper(substr($uniqueCode, 0, 3));
        $numericCode = self::$sequence;

        return [
            'code' => $code,
            'code_alpha3' => $code3,
            'numeric_code' => $numericCode,
            'name' => fake()->unique()->country(),
            'native_name' => [fake()->word()],
            'capital' => fake()->city(),
            'region' => fake()->randomElement(['Africa', 'Americas', 'Asia', 'Europe', 'Oceania']),
            'subregion' => fake()->word(),
            'currency_code' => fake()->currencyCode(),
            'currency_name' => fake()->word(),
            'currency_symbol' => fake()->randomElement(['$', '€', '£', '¥']),
            'phone_code' => '+'.fake()->numberBetween(1, 999),
            'flag_emoji' => '🏳️',
            'flag_svg' => null,
            'languages' => [fake()->languageCode()],
            'tld' => '.'.strtolower($code),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'is_active' => true,
            'is_eu_member' => fake()->boolean(),
            'display_order' => fake()->numberBetween(1, 999),
            'metadata' => null,
        ];
    }
}
