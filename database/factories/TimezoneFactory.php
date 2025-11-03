<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Timezone>
 */
class TimezoneFactory extends Factory
{
    private static int $sequence = 0;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $offset = fake()->randomElement([-12, -11, -10, -9, -8, -7, -6, -5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]) * 3600;
        $region = fake()->randomElement(['Africa', 'America', 'Antarctica', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific']);

        // Generate unique abbreviations using atomic counter
        self::$sequence++;
        $random = bin2hex(random_bytes(1));
        $uniqueCode = str_pad(dechex(self::$sequence), 2, '0', STR_PAD_LEFT).$random;

        $abbrev = strtoupper(substr($uniqueCode, 0, 3));
        $abbrevDst = strtoupper(substr($uniqueCode, 1, 3));
        $cityName = fake()->unique()->city();

        return [
            'name' => "{$region}/{$cityName}_".self::$sequence,
            'abbreviation' => $abbrev,
            'abbreviation_dst' => $abbrevDst,
            'offset' => $offset,
            'offset_dst' => $offset + 3600,
            'offset_formatted' => 'UTC'.($offset >= 0 ? '+' : '').gmdate('H:i', abs($offset)),
            'uses_dst' => fake()->boolean(),
            'display_name' => fake()->unique()->words(2, true),
            'city_name' => $cityName,
            'region' => $region,
            'coordinates' => null,
            'population' => fake()->numberBetween(100000, 10000000),
            'is_primary' => false,
            'is_active' => true,
            'display_order' => fake()->numberBetween(1, 999),
            'metadata' => null,
        ];
    }
}
