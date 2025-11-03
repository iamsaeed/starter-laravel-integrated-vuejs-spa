<?php

namespace App\Services;

use App\Exceptions\EmailTemplateRenderException;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailTemplateService
{
    public function __construct(
        protected BladeTemplateSecurityService $securityService
    ) {}

    public function render(string $templateKey, array $data): array
    {
        $template = EmailTemplate::query()
            ->where('key', $templateKey)
            ->where('is_active', true)
            ->firstOrFail();

        // Validate template security before rendering
        $this->validateTemplate($template);

        try {
            // Render subject
            $subject = Blade::render($template->subject_template, $data);

            // Render complete body content (includes full HTML structure)
            $html = Blade::render($template->body_content, $data);

            return [
                'subject' => trim($subject),
                'html' => $html,
            ];

        } catch (\Throwable $e) {
            Log::error('Email template rendering failed', [
                'template_key' => $templateKey,
                'error' => $e->getMessage(),
            ]);

            throw new EmailTemplateRenderException(
                "Failed to render email template: {$templateKey}",
                previous: $e
            );
        }
    }

    public function preview(EmailTemplate $template, array $sampleData): array
    {
        // Validate security before preview
        $this->validateTemplate($template);

        try {
            $subject = Blade::render($template->subject_template, $sampleData);
            $html = Blade::render($template->body_content, $sampleData);

            return [
                'subject' => trim($subject),
                'html' => $html,
            ];
        } catch (\Throwable $e) {
            throw new EmailTemplateRenderException(
                "Failed to preview email template: {$template->key}",
                previous: $e
            );
        }
    }

    public function sendTest(EmailTemplate $template, array $emails, array $sampleData): void
    {
        $rendered = $this->preview($template, $sampleData);

        foreach ($emails as $email) {
            Mail::html($rendered['html'], function ($message) use ($rendered, $email) {
                $message->to($email)
                    ->subject('[TEST] '.$rendered['subject']);
            });
        }
    }

    protected function validateTemplate(EmailTemplate $template): void
    {
        $this->securityService->validate($template->body_content);
        $this->securityService->validate($template->subject_template);
    }
}
