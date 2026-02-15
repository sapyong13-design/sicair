<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceAdjustment extends Model
{
    protected $fillable = [
        'user_id',
        'year',
        'jenis_cuti',
        'adjustment_days',
        'reason',
        'type',
        'status',
        'approved_by',
        'approval_note',
        'approved_at',
    ];

    protected $casts = [
        'adjustment_days' => 'integer',
        'year' => 'integer',
        'approved_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const TYPE_ADDITION = 'addition';
    const TYPE_DEDUCTION = 'deduction';
    const TYPE_CORRECTION = 'correction';

    /**
     * Get the user this adjustment belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who approved this adjustment
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
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
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_ADDITION => 'Penambahan',
            self::TYPE_DEDUCTION => 'Pengurangan',
            self::TYPE_CORRECTION => 'Koreksi',
            default => $this->type,
        };
    }
}
