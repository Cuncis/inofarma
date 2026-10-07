<?php

namespace App\Mail;

use App\Models\Newsletter;
use App\Models\Subscriber;
use App\Support\Newsletters\NewsletterRenderer;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/**
 * One newsletter to one person. Every copy carries that person's own
 * unsubscribe link, in the footer and in the List-Unsubscribe headers mail
 * apps use for their "Unsubscribe" button.
 */
class NewsletterMail extends Mailable
{
    public function __construct(
        public Newsletter $newsletter,
        public Subscriber $subscriber,
        public bool $isTest = false,
    ) {}

    public function unsubscribeUrl(): string
    {
        return route('newsletter.unsubscribe', $this->subscriber->unsubscribe_token);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: ($this->isTest ? '[TEST] ' : '').$this->newsletter->subject,
        );
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<'.$this->unsubscribeUrl().'>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter',
            with: [
                'subject' => $this->newsletter->subject,
                'previewText' => $this->newsletter->preview_text,
                ...NewsletterRenderer::render($this->newsletter),
                'unsubscribeUrl' => $this->unsubscribeUrl(),
                'isTest' => $this->isTest,
            ],
        );
    }
}
