<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'model',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a model action
     */
    public static function log(
        string $action,
        string $model,
        int $modelId,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'model' => $model,
            'model_id' => $modelId,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get action label in Indonesian
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'create'                       => 'Dibuat',
            'update'                       => 'Diubah',
            'delete'                       => 'Dihapus',
            'approve'                      => 'Disetujui',
            'reject'                       => 'Ditolak',
            'login'                        => 'Login',
            'logout'                       => 'Logout',
            'approve_appeal'               => 'Setujui Banding',
            'reject_appeal'                => 'Tolak Banding',
            'approve_amendment'            => 'Setujui Perubahan Cuti',
            'reject_amendment'             => 'Tolak Perubahan Cuti',
            'approve_balance_adjustment'   => 'Setujui Penyesuaian Saldo',
            'reject_balance_adjustment'    => 'Tolak Penyesuaian Saldo',
            'balance_deducted'             => 'Saldo Dikurangi',
            'balance_restored'             => 'Saldo Dipulihkan',
            default                        => ucwords(str_replace('_', ' ', $this->action)),
        };
    }
}
