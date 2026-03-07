<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin       = 'admin';
    case Ketua       = 'ketua';
    case WakilKetua  = 'wakil_ketua';
    case Atasan      = 'atasan';
    case Hakim       = 'hakim';
    case HakimAdHoc  = 'hakim_adhoc';
    case Panitera    = 'panitera';
    case Sekretaris  = 'sekretaris';
    case Kepegawaian = 'kepegawaian';
    case Pegawai     = 'pegawai';

    public function label(): string
    {
        return match($this) {
            self::Admin       => 'Administrator',
            self::Ketua       => 'Ketua',
            self::WakilKetua  => 'Wakil Ketua',
            self::Atasan      => 'Atasan',
            self::Hakim       => 'Hakim',
            self::HakimAdHoc  => 'Hakim Ad Hoc',
            self::Panitera    => 'Panitera',
            self::Sekretaris  => 'Sekretaris',
            self::Kepegawaian => 'Kepegawaian',
            self::Pegawai     => 'Pegawai',
        };
    }

    public function canApproveAsAtasan(): bool
    {
        return in_array($this, [self::Atasan, self::Ketua, self::WakilKetua]);
    }

    public function canApproveAsPejabat(): bool
    {
        return in_array($this, [self::Ketua, self::WakilKetua, self::Panitera, self::Sekretaris]);
    }

    public function isAdmin(): bool
    {
        return $this === self::Admin;
    }
}
