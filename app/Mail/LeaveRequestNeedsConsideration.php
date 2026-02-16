<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveRequestNeedsConsideration extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public LeaveRequest $leaveRequest
    ) {
        $this->onQueue('mails');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        // Send to ketua (head of organization)
        $ketua = User::where('role', 'ketua')->first();

        // If no ketua found, try to find admin, fallback to from address
        if (!$ketua) {
            $ketua = User::where('role', 'admin')->first();
        }

        $recipientEmail = $ketua?->email ?? config('mail.from.address');

        return new Envelope(
            to: $recipientEmail,
            subject: '⏳ Pengajuan Cuti Memerlukan Pertimbangan - ' . $this->leaveRequest->type_label,
            from: config('mail.from.address'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.leave-request-needs-consideration',
            with: [
                'leaveRequest' => $this->leaveRequest,
            ],
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
