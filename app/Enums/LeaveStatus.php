<?php

namespace App\Enums;

enum LeaveStatus: string
{
    case Diajukan           = 'diajukan';
    case PertimbanganAtasan = 'pertimbangan_atasan';
    case Approved           = 'disetujui';
    case Rejected           = 'ditolak';
    case Cancelled          = 'dibatalkan';
    case Revised            = 'revisi';
    case Ditangguhkan       = 'ditangguhkan';

    public function label(): string
    {
        return match($this) {
            self::Diajukan           => 'Menunggu Review',
            self::PertimbanganAtasan => 'Pertimbangan Atasan',
            self::Approved           => 'Disetujui',
            self::Rejected           => 'Ditolak',
            self::Cancelled          => 'Dibatalkan',
            self::Revised            => 'Perlu Revisi',
            self::Ditangguhkan       => 'Ditangguhkan',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Diajukan, self::PertimbanganAtasan, self::Ditangguhkan => 'sh-badge-pending',
            self::Approved                                                => 'sh-badge-approved',
            self::Rejected                                                => 'sh-badge-rejected',
            self::Cancelled                                               => 'sh-badge-cancelled',
            self::Revised                                                 => 'sh-badge-revised',
        };
    }

    public static function fromLabel(string $label): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->label() === $label) return $case;
        }
        return null;
    }
}
