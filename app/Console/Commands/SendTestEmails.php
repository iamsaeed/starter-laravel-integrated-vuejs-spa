<?php

namespace App\Console\Commands;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendTestEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-test
                            {template_id? : Email template ID to send (sends all active templates if not provided)}
                            {--email= : Email address to send test emails to (defaults to admin email)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test email for a specific template or all active email templates with demo data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $templateId = $this->argument('template_id');
        $recipientEmail = $this->option('email') ?? User::first()->email ?? 'admin@example.com';

        $this->info("Sending test emails to: {$recipientEmail}");
        $this->newLine();

        // If template ID is provided, send only that template
        if ($templateId) {
            return $this->sendSingleTemplate($templateId, $recipientEmail);
        }

        // Otherwise send all active templates
        return $this->sendAllTemplates($recipientEmail);
    }

    /**
     * Send a single template by ID
     */
    protected function sendSingleTemplate(int $templateId, string $recipientEmail): int
    {
        $template = EmailTemplate::find($templateId);

        if (! $template) {
            $this->error("Email template with ID {$templateId} not found.");

            return self::FAILURE;
        }

        if (! $template->is_active) {
            $this->warn("Warning: Template '{$template->name}' is not active.");
        }

        $this->line("Processing template: {$template->name} ({$template->key})...");

        try {
            $this->sendTemplateTest($template, $recipientEmail);
            $this->info('✓ Sent successfully');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("✗ Failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Send all active templates
     */
    protected function sendAllTemplates(string $recipientEmail): int
    {
        $templates = EmailTemplate::query()
            ->where('is_active', true)
            ->get();

        if ($templates->isEmpty()) {
            $this->error('No active email templates found.');

            return self::FAILURE;
        }

        $successCount = 0;
        $failureCount = 0;

        foreach ($templates as $index => $template) {
            $this->line("Processing template: {$template->name} ({$template->key})...");

            try {
                $this->sendTemplateTest($template, $recipientEmail);
                $this->info('✓ Sent successfully');
                $successCount++;

                // Wait 60 seconds before sending next email (except for last one)
                if (($index + 1) < $templates->count()) {
                    $this->line('   Waiting 60 seconds before next email to avoid rate limits...');
                    sleep(60);
                }
            } catch (\Throwable $e) {
                $this->error("✗ Failed: {$e->getMessage()}");
                $failureCount++;
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info('Summary:');
        $this->line("  - Successfully sent: {$successCount}");
        $this->line("  - Failed: {$failureCount}");
        $this->line("  - Total templates: {$templates->count()}");

        return self::SUCCESS;
    }

    /**
     * Send a test email for a specific template with demo data
     */
    protected function sendTemplateTest(EmailTemplate $template, string $recipientEmail): void
    {
        $sampleData = $this->getSampleDataForTemplate($template->key);

        $subject = \Blade::render($template->subject_template, $sampleData);
        $html = \Blade::render($template->body_content, $sampleData);

        Mail::html($html, function ($message) use ($subject, $recipientEmail) {
            $message->to($recipientEmail)
                ->subject('[TEST] '.$subject);
        });
    }

    /**
     * Get sample data for each template type
     */
    protected function getSampleDataForTemplate(string $templateKey): array
    {
        $demoUser = $this->createDemoUser();

        return match ($templateKey) {
            'user_welcome' => [
                'user' => $demoUser,
                'verification_url' => url('/verify-email/demo-token'),
            ],
            'password_reset', 'forgot_password' => [
                'user' => $demoUser,
                'reset_url' => url('/reset-password/demo-token'),
                'expires_in' => '60 minutes',
            ],
            // workspace_invitation removed (workspace functionality was removed)
            default => [
                'user' => $demoUser,
            ],
        };
    }

    /**
     * Create a demo user object
     */
    protected function createDemoUser(): object
    {
        // Try to get real user first
        $realUser = User::first();

        if ($realUser) {
            return $realUser;
        }

        // Otherwise create a demo object
        return (object) [
            'id' => 1,
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'created_at' => now(),
        ];
    }

}
