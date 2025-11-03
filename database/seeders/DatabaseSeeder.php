<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed reference data first
        $this->call([
            CountriesSeeder::class,
            TimezonesSeeder::class,
            CountryTimezoneSeeder::class,
            SettingListsSeeder::class,
            EmailTemplatesSeeder::class,
        ]);

        $users = ['admin', 'user'];

        foreach ($users as $role) {
            $user = User::factory()->create([
                'name' => ucfirst($role),
                'email' => "{$role}@app.com",
                'password' => bcrypt('password'),
            ]);

            // Assign role to user
            $user->assignRole($role);
        }
    }
}
