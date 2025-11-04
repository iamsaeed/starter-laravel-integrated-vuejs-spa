<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ResetApplicationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset
                            {--force : Force reset without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset application: Clean storage/media/logs/caches, fresh migrate and seed (local environment only)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Security check: Only allow in local environment
        if (! app()->environment('local')) {
            $this->error('This command can only be run in local environment!');
            $this->warn('Current environment: '.app()->environment());

            return self::FAILURE;
        }

        // Confirmation prompt (unless --force is used)
        if (! $this->option('force')) {
            if (! $this->confirm('This will DELETE ALL DATA and reset the application. Are you sure?', false)) {
                $this->info('Reset cancelled.');

                return self::SUCCESS;
            }
        }

        $this->newLine();
        $this->line('🔄 Starting application reset...');
        $this->newLine();

        // Step 1: Clean storage files and media
        $this->cleanStorageFiles();

        // Step 2: Clean log files
        $this->cleanLogFiles();

        // Step 3: Clear application caches
        $this->clearCaches();

        // Step 4: Fresh migrate database
        $this->freshMigrateDatabase();

        // Step 5: Seed database
        $this->seedDatabase();

        $this->newLine();
        $this->info('✅ Application reset completed successfully!');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Fresh migrate the database.
     */
    protected function freshMigrateDatabase(): void
    {
        $this->info('🔄 Running fresh migrations on database...');

        Artisan::call('migrate:fresh', [
            '--force' => true,
        ]);

        $output = trim(Artisan::output());
        if ($output) {
            $this->line($output);
        }

        $this->info('  ✓ Migrations completed');
        $this->newLine();
    }

    /**
     * Seed the database.
     */
    protected function seedDatabase(): void
    {
        $this->info('🌱 Seeding database...');

        Artisan::call('db:seed', [
            '--force' => true,
        ]);

        $output = trim(Artisan::output());
        if ($output) {
            $this->line($output);
        }

        $this->info('  ✓ Seeding completed');
        $this->newLine();
    }

    /**
     * Clean storage files and media.
     */
    protected function cleanStorageFiles(): void
    {
        $this->info('🗑️  Cleaning storage files and media...');

        $deletedCount = 0;

        // Clean media library directory
        $mediaLibraryPath = storage_path('media-library');
        if (File::isDirectory($mediaLibraryPath)) {
            File::deleteDirectory($mediaLibraryPath);
            File::makeDirectory($mediaLibraryPath, 0755, true);
            $deletedCount++;
            $this->line('  ✓ Cleaned: media-library');
        }

        // Clean app storage directories (except .gitignore)
        $appStorageDirs = ['public', 'private'];
        foreach ($appStorageDirs as $dir) {
            $dirPath = storage_path("app/{$dir}");
            if (File::isDirectory($dirPath)) {
                File::deleteDirectory($dirPath);
                File::makeDirectory($dirPath, 0755, true);

                // Restore .gitignore
                File::put($dirPath.'/.gitignore', "*\n!.gitignore\n");

                $deletedCount++;
                $this->line('  ✓ Cleaned: app/'.$dir);
            }
        }

        $this->info("  ✓ Cleaned {$deletedCount} storage location(s)");
        $this->newLine();
    }

    /**
     * Clean log files.
     */
    protected function cleanLogFiles(): void
    {
        $this->info('📝 Cleaning log files...');

        $deletedCount = 0;

        // List of log files to delete
        $logFiles = [
            storage_path('logs/laravel.log'),
            storage_path('logs/browser.log'),
        ];

        foreach ($logFiles as $logFile) {
            if (File::exists($logFile)) {
                File::delete($logFile);
                $deletedCount++;
                $this->line('  ✓ Deleted: '.basename($logFile));
            }
        }

        if ($deletedCount === 0) {
            $this->warn('  No log files found to delete.');
        } else {
            $this->info("  ✓ Deleted {$deletedCount} log file(s)");
        }

        $this->newLine();
    }

    /**
     * Clear all application caches.
     */
    protected function clearCaches(): void
    {
        $this->info('🧹 Clearing application caches...');

        $caches = [
            'config' => 'Configuration cache',
            'route' => 'Route cache',
            'view' => 'View cache',
            'event' => 'Event cache',
        ];

        foreach ($caches as $cache => $description) {
            Artisan::call("$cache:clear");
            $this->line("  ✓ Cleared: $description");
        }

        // Clear application cache
        Artisan::call('cache:clear');
        $this->line('  ✓ Cleared: Application cache');

        // Clear compiled classes and services
        if (File::exists(base_path('bootstrap/cache/compiled.php'))) {
            File::delete(base_path('bootstrap/cache/compiled.php'));
            $this->line('  ✓ Cleared: Compiled classes');
        }

        if (File::exists(base_path('bootstrap/cache/config.php'))) {
            File::delete(base_path('bootstrap/cache/config.php'));
            $this->line('  ✓ Cleared: Cached config file');
        }

        $this->info('  ✓ All caches cleared');
        $this->newLine();
    }
}
