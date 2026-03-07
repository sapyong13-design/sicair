<?php

namespace App\Models;

use App\Mail\LeaveRequestApproved;
use App\Mail\LeaveRequestNeedsConsiderationMail;
use App\Mail\LeaveRequestRejected;
use App\Mail\LeaveRequestSubmitted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'title', 'message', 'type', 'link', 'is_read', 'data',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'data' => 'array',
        ];
    }

    const TYPE_CUTI_DIAJUKAN = 'cuti_diajukan';
    const TYPE_CUTI_DISETUJUI = 'cuti_disetujui';
    const TYPE_CUTI_DITOLAK = 'cuti_ditolak';
    const TYPE_CUTI_PERTIMBANGAN = 'cuti_pertimbangan';
    const TYPE_INFO = 'info';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public static function kirim(int $userId, string $title, string $message, string $type = 'info', ?string $link = null, ?LeaveRequest $leaveRequest = null): self
    {
        $notification = self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
        ]);

        // Trigger email notification if user has email and LeaveRequest is provided
        // #48: Respect user's notification preferences
        if ($leaveRequest && $notification->user?->email) {
            $prefKey = match ($type) {
                self::TYPE_CUTI_DISETUJUI    => 'email_on_approve',
                self::TYPE_CUTI_DITOLAK      => 'email_on_reject',
                self::TYPE_CUTI_DIAJUKAN     => 'email_on_pending',
                self::TYPE_CUTI_PERTIMBANGAN => 'email_on_decision',
                default => null,
            };
            if (!$prefKey || $notification->user->wantsEmailNotification($prefKey)) {
                self::sendEmailNotification($notification->user->email, $type, $leaveRequest);
            }
        }

        return $notification;
    }

    /**
     * Send email notification based on type
     */
    private static function sendEmailNotification(string $email, string $type, LeaveRequest $leaveRequest): void
    {
        $mailable = match ($type) {
            self::TYPE_CUTI_DIAJUKAN => new LeaveRequestSubmitted($leaveRequest),
            self::TYPE_CUTI_DISETUJUI => new LeaveRequestApproved($leaveRequest, auth()->user()->name ?? 'Admin'),
            self::TYPE_CUTI_DITOLAK => new LeaveRequestRejected($leaveRequest, auth()->user()->name ?? 'Admin', $leaveRequest->catatan_atasan),
            self::TYPE_CUTI_PERTIMBANGAN => new LeaveRequestNeedsConsiderationMail($leaveRequest),
            default => null,
        };

        if ($mailable) {
            Mail::queue($mailable->to($email));
        }
    }
}
