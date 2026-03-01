<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DinasLuar extends Model
{
    protected $table = 'dinas_luar';

    protected $fillable = [
        'user_id',
        'tujuan',
        'keperluan',
        'start_date',
        'end_date',
        'dokumen',
        'keterangan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDurasiAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}
