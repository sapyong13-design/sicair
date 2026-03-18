<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $notifSubject,
        public readonly string $body
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->notifSubject);
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.leave-notification');
    }
}
