<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Timezone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryTimezoneSeeder extends Seeder
{
    public function run(): void
    {
        $relationships = [
            // United States - Multiple timezones
            ['country_code' => 'US', 'timezone_name' => 'America/New_York', 'is_primary' => true, 'regions' => ['NY', 'FL', 'MA', 'PA', 'GA', 'VA', 'NC', 'SC', 'DC']],
            ['country_code' => 'US', 'timezone_name' => 'America/Chicago', 'is_primary' => false, 'regions' => ['IL', 'TX', 'MO', 'WI', 'TN', 'AL', 'LA', 'OK', 'KS']],
            ['country_code' => 'US', 'timezone_name' => 'America/Denver', 'is_primary' => false, 'regions' => ['CO', 'MT', 'UT', 'WY', 'NM']],
            ['country_code' => 'US', 'timezone_name' => 'America/Los_Angeles', 'is_primary' => false, 'regions' => ['CA', 'WA', 'NV', 'OR']],
            ['country_code' => 'US', 'timezone_name' => 'America/Phoenix', 'is_primary' => false, 'regions' => ['AZ']],
            ['country_code' => 'US', 'timezone_name' => 'America/Anchorage', 'is_primary' => false, 'regions' => ['AK']],
            ['country_code' => 'US', 'timezone_name' => 'Pacific/Honolulu', 'is_primary' => false, 'regions' => ['HI']],

            // Canada - Multiple timezones
            ['country_code' => 'CA', 'timezone_name' => 'America/Toronto', 'is_primary' => true, 'regions' => ['ON', 'QC']],
            ['country_code' => 'CA', 'timezone_name' => 'America/Chicago', 'is_primary' => false, 'regions' => ['MB', 'SK']],
            ['country_code' => 'CA', 'timezone_name' => 'America/Denver', 'is_primary' => false, 'regions' => ['AB']],
            ['country_code' => 'CA', 'timezone_name' => 'America/Los_Angeles', 'is_primary' => false, 'regions' => ['BC', 'YT']],

            // United Kingdom
            ['country_code' => 'GB', 'timezone_name' => 'Europe/London', 'is_primary' => true, 'regions' => null],

            // Australia - Multiple timezones
            ['country_code' => 'AU', 'timezone_name' => 'Australia/Sydney', 'is_primary' => true, 'regions' => ['NSW', 'VIC', 'TAS', 'ACT']],

            // Germany
            ['country_code' => 'DE', 'timezone_name' => 'Europe/Berlin', 'is_primary' => true, 'regions' => null],

            // France
            ['country_code' => 'FR', 'timezone_name' => 'Europe/Paris', 'is_primary' => true, 'regions' => null],

            // Spain
            ['country_code' => 'ES', 'timezone_name' => 'Europe/Madrid', 'is_primary' => true, 'regions' => null],

            // Italy
            ['country_code' => 'IT', 'timezone_name' => 'Europe/Rome', 'is_primary' => true, 'regions' => null],

            // Japan
            ['country_code' => 'JP', 'timezone_name' => 'Asia/Tokyo', 'is_primary' => true, 'regions' => null],

            // China
            ['country_code' => 'CN', 'timezone_name' => 'Asia/Shanghai', 'is_primary' => true, 'regions' => null],

            // India
            ['country_code' => 'IN', 'timezone_name' => 'Asia/Kolkata', 'is_primary' => true, 'regions' => null],

            // Brazil - Multiple timezones
            ['country_code' => 'BR', 'timezone_name' => 'America/Sao_Paulo', 'is_primary' => true, 'regions' => ['SP', 'RJ', 'MG']],
            ['country_code' => 'BR', 'timezone_name' => 'America/Bogota', 'is_primary' => false, 'regions' => ['AM', 'AC', 'RO', 'RR']],

            // Mexico
            ['country_code' => 'MX', 'timezone_name' => 'America/Mexico_City', 'is_primary' => true, 'regions' => null],

            // Netherlands
            ['country_code' => 'NL', 'timezone_name' => 'Europe/Amsterdam', 'is_primary' => true, 'regions' => null],

            // Switzerland
            ['country_code' => 'CH', 'timezone_name' => 'Europe/Berlin', 'is_primary' => true, 'regions' => null],

            // Sweden
            ['country_code' => 'SE', 'timezone_name' => 'Europe/Stockholm', 'is_primary' => true, 'regions' => null],

            // Norway
            ['country_code' => 'NO', 'timezone_name' => 'Europe/Berlin', 'is_primary' => true, 'regions' => null],

            // Denmark
            ['country_code' => 'DK', 'timezone_name' => 'Europe/Berlin', 'is_primary' => true, 'regions' => null],

            // Poland
            ['country_code' => 'PL', 'timezone_name' => 'Europe/Berlin', 'is_primary' => true, 'regions' => null],

            // Belgium
            ['country_code' => 'BE', 'timezone_name' => 'Europe/Brussels', 'is_primary' => true, 'regions' => null],

            // Austria
            ['country_code' => 'AT', 'timezone_name' => 'Europe/Berlin', 'is_primary' => true, 'regions' => null],

            // Portugal
            ['country_code' => 'PT', 'timezone_name' => 'Europe/London', 'is_primary' => true, 'regions' => null],

            // Greece
            ['country_code' => 'GR', 'timezone_name' => 'Europe/Istanbul', 'is_primary' => true, 'regions' => null],

            // Ireland
            ['country_code' => 'IE', 'timezone_name' => 'Europe/London', 'is_primary' => true, 'regions' => null],

            // South Korea
            ['country_code' => 'KR', 'timezone_name' => 'Asia/Seoul', 'is_primary' => true, 'regions' => null],

            // Singapore
            ['country_code' => 'SG', 'timezone_name' => 'Asia/Singapore', 'is_primary' => true, 'regions' => null],

            // New Zealand
            ['country_code' => 'NZ', 'timezone_name' => 'Pacific/Auckland', 'is_primary' => true, 'regions' => null],

            // Russia - Multiple timezones
            ['country_code' => 'RU', 'timezone_name' => 'Europe/Moscow', 'is_primary' => true, 'regions' => ['MOW', 'SPE']],

            // South Africa
            ['country_code' => 'ZA', 'timezone_name' => 'Africa/Johannesburg', 'is_primary' => true, 'regions' => null],

            // Argentina
            ['country_code' => 'AR', 'timezone_name' => 'America/Buenos_Aires', 'is_primary' => true, 'regions' => null],

            // Colombia
            ['country_code' => 'CO', 'timezone_name' => 'America/Bogota', 'is_primary' => true, 'regions' => null],

            // Peru
            ['country_code' => 'PE', 'timezone_name' => 'America/Lima', 'is_primary' => true, 'regions' => null],

            // Egypt
            ['country_code' => 'EG', 'timezone_name' => 'Africa/Cairo', 'is_primary' => true, 'regions' => null],

            // Nigeria
            ['country_code' => 'NG', 'timezone_name' => 'Africa/Lagos', 'is_primary' => true, 'regions' => null],

            // Kenya
            ['country_code' => 'KE', 'timezone_name' => 'Africa/Nairobi', 'is_primary' => true, 'regions' => null],

            // Hong Kong
            ['country_code' => 'HK', 'timezone_name' => 'Asia/Hong_Kong', 'is_primary' => true, 'regions' => null],

            // United Arab Emirates
            ['country_code' => 'AE', 'timezone_name' => 'Asia/Dubai', 'is_primary' => true, 'regions' => null],

            // Thailand
            ['country_code' => 'TH', 'timezone_name' => 'Asia/Bangkok', 'is_primary' => true, 'regions' => null],

            // Indonesia
            ['country_code' => 'ID', 'timezone_name' => 'Asia/Jakarta', 'is_primary' => true, 'regions' => null],

            // Pakistan
            ['country_code' => 'PK', 'timezone_name' => 'Asia/Karachi', 'is_primary' => true, 'regions' => null],

            // Fiji
            ['country_code' => 'FJ', 'timezone_name' => 'Pacific/Fiji', 'is_primary' => true, 'regions' => null],

            // Turkey
            ['country_code' => 'TR', 'timezone_name' => 'Europe/Istanbul', 'is_primary' => true, 'regions' => null],
        ];

        foreach ($relationships as $relationship) {
            $country = Country::where('code', $relationship['country_code'])->first();
            $timezone = Timezone::where('name', $relationship['timezone_name'])->first();

            if ($country && $timezone) {
                // Check if relationship already exists
                $exists = DB::table('country_timezone')
                    ->where('country_id', $country->id)
                    ->where('timezone_id', $timezone->id)
                    ->exists();

                if (! $exists) {
                    DB::table('country_timezone')->insert([
                        'country_id' => $country->id,
                        'timezone_id' => $timezone->id,
                        'is_primary' => $relationship['is_primary'],
                        'regions' => $relationship['regions'] ? json_encode($relationship['regions']) : null,
                        'notes' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
