<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiRecord extends Model
{
    protected $fillable = [
        'user_id',
        'tahun',
        'hak_cuti',
        'cuti_diambil',
        'sisa_cuti',
        'carry_over',
        'tambahan_terpencil',
        'ditangguhkan',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'ditangguhkan' => 'boolean',
        ];
    }

    public function getTotalHakAttribute(): int
    {
        return $this->hak_cuti + $this->carry_over + $this->tambahan_terpencil;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
