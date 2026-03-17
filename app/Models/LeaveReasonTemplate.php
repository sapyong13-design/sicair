<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveReasonTemplate extends Model
{
    protected $fillable = ['label', 'body', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
