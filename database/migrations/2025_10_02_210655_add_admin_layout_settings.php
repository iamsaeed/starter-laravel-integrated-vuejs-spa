<?php

use Database\Seeders\SettingListsSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Seed admin layout options and default setting
        $this->call(SettingListsSeeder::class);
        $this->call(SettingsSeeder::class);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove admin layout settings
        \App\Models\SettingList::where('key', 'admin_layouts')->delete();
        \App\Models\Setting::where('key', 'default_admin_layout')->delete();
    }

    private function call(string $seeder): void
    {
        (new $seeder)->run();
    }
};
