<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public static function kirim(int $userId, string $title, string $message, string $type = 'info', ?string $link = null): self
    {
        return self::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
        ]);
    }
}
