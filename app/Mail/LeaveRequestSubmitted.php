<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestSubmitted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest
    ) {
        $this->onQueue('mails');
    }

    public function to(): array|string
    {
        return $this->leaveRequest->user->email;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✉️ Pengajuan Cuti Baru - ' . $this->leaveRequest->user->name,
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.leave-request-submitted',
            with: [
                'leaveRequest' => $this->leaveRequest,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
