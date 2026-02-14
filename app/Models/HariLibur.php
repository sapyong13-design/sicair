<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $table = 'hari_libur';

    protected $fillable = [
        'tanggal',
        'keterangan',
        'tahun',
        'is_cuti_bersama',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'is_cuti_bersama' => 'boolean',
        ];
    }

    public static function isHoliday($date): bool
    {
        return static::where('tanggal', $date)->exists();
    }

    public static function getHolidaysInRange($startDate, $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereBetween('tanggal', [$startDate, $endDate])->get();
    }

    public static function getHolidaysForYear(int $year): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('tahun', $year)->orderBy('tanggal')->get();
    }
}
