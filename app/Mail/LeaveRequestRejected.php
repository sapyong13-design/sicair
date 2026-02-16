<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestRejected extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public LeaveRequest $leaveRequest,
        public string $rejectorName = '',
        public string $reason = ''
    ) {
        $this->onQueue('mails');
    }

    public function envelope(): Envelope
    {
        $userEmail = $this->leaveRequest->user?->email ?? config('mail.from.address');

        return new Envelope(
            to: $userEmail,
            subject: '❌ Pengajuan Cuti Ditolak - ' . $this->leaveRequest->type_label,
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.leave-request-rejected',
            with: [
                'leaveRequest' => $this->leaveRequest,
                'rejectorName' => $this->rejectorName ?: $this->leaveRequest->atasanReviewer?->name ?? $this->leaveRequest->pejabat?->name ?? 'Reviewer',
                'reason' => $this->reason ?: $this->leaveRequest->catatan_atasan ?: $this->leaveRequest->catatan_pejabat,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
