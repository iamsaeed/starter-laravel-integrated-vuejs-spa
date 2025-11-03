<?php

namespace App\Console\Commands;

use App\Services\MediaTransferService;
use Illuminate\Console\Command;

class CleanupTempImageUploadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'temp-images:cleanup {--hours=24 : Delete temp images older than this many hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup old temporary image uploads that were not transferred to tasks';

    /**
     * Execute the console command.
     */
    public function handle(MediaTransferService $mediaTransferService): int
    {
        $hours = (int) $this->option('hours');

        $this->info("Cleaning up temporary images older than {$hours} hours...");

        $count = $mediaTransferService->cleanupOldTempImages($hours);

        if ($count === 0) {
            $this->info('No temporary images found to cleanup.');
        } else {
            $this->info("Successfully cleaned up {$count} temporary image(s).");
        }

        return self::SUCCESS;
    }
}
