<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Email $email,
        public string $recipientName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->email->sender->email, $this->email->sender->name),
            subject: $this->email->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter',
            with: [
                'body' => $this->email->body,
                'recipientName' => $this->recipientName,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (empty($this->email->attachments)) {
            return [];
        }

        return collect($this->email->attachments)
            ->map(fn (string $path): Attachment => Attachment::fromStorageDisk('public', $path))
            ->all();
    }
}
