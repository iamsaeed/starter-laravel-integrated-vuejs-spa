<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Example Mailable that uses database-stored email templates.
 *
 * Usage:
 * Mail::to($user)->queue(new TemplatedMail('user_welcome', [
 *     'user' => $user,
 *     'verification_url' => $verificationUrl,
 * ]));
 */
class TemplatedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected array $rendered;

    /**
     * Create a new message instance.
     */
    public function __construct(
        protected string $templateKey,
        protected array $data
    ) {
        $this->rendered = app(EmailTemplateService::class)->render(
            $this->templateKey,
            $this->data
        );
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->rendered['subject'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: $this->rendered['html'],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
