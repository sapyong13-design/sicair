<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveAppeal extends Model
{
    protected $fillable = [
        'leave_request_id',
        'appealed_by',
        'decided_by',
        'reason',
        'additional_info',
        'status',
        'decision_note',
        'decision',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const DECISION_APPROVED = 'approved';
    const DECISION_DENIED = 'denied';

    /**
     * Get the leave request being appealed
     */
    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    /**
     * Get the user who filed the appeal
     */
    public function appellant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appealed_by');
    }

    /**
     * Get the admin who decided the appeal
     */
    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /**
     * Check if pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get decision display text
     */
    public function getDecisionLabelAttribute(): string
    {
        return match ($this->decision) {
            self::DECISION_APPROVED => 'Disetujui Kembali',
            self::DECISION_DENIED => 'Tetap Ditolak',
            default => 'Pending',
        };
    }
}
