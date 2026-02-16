<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $approverName = ''
    ) {
        $this->onQueue('mails');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->leaveRequest->user->email,
            subject: '✅ Pengajuan Cuti Disetujui - ' . $this->leaveRequest->type_label,
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.leave-request-approved',
            with: [
                'leaveRequest' => $this->leaveRequest,
                'approverName' => $this->approverName ?: $this->leaveRequest->pejabat->name ?? 'Pejabat',
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
