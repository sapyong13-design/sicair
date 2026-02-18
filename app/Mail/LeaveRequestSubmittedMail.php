<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestSubmittedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public LeaveRequest $leaveRequest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pengajuan Cuti Baru - {$this->leaveRequest->user->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.leave-request-submitted',
        );
    }
}
