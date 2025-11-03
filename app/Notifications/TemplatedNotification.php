<?php

namespace App\Notifications;

use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Generic notification class for database-stored email templates.
 *
 * Usage:
 * $user->notify(new TemplatedNotification('user_welcome', [
 *     'verification_url' => $url,
 * ]));
 *
 * This single class works with ANY email template in your database.
 * Just pass the template key and the data you want to pass to the template.
 */
class TemplatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $rendered;

    /**
     * Create a new notification instance.
     *
     * @param  string  $templateKey  The email template key (e.g., 'user_welcome', 'password_reset')
     * @param  array  $data  Additional data to pass to the template (merged with $notifiable)
     */
    public function __construct(
        protected string $templateKey,
        protected array $data = []
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Merge notifiable (user) with additional data
        $templateData = array_merge([
            'user' => $notifiable,
        ], $this->data);

        // Render the template
        $this->rendered = app(EmailTemplateService::class)->render(
            $this->templateKey,
            $templateData
        );

        return (new MailMessage)
            ->subject($this->rendered['subject'])
            ->view('emails.raw-html', ['htmlContent' => $this->rendered['html']]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'template_key' => $this->templateKey,
            'data' => $this->data,
        ];
    }

    /**
     * Get the template key (useful for testing).
     */
    public function getTemplateKey(): string
    {
        return $this->templateKey;
    }

    /**
     * Get the template data (useful for testing).
     */
    public function getData(): array
    {
        return $this->data;
    }
}
