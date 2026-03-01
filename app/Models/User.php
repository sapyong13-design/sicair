<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name', 'nip', 'email', 'password', 'role', 'leave_balance',
        'jabatan', 'golongan_ruang', 'unit_kerja', 'masa_kerja_mulai',
        'status_pegawai', 'jenis_kelamin', 'jumlah_anak', 'lokasi_terpencil',
        'atasan_id', 'telepon', 'alamat',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'leave_balance' => 'integer',
            'masa_kerja_mulai' => 'date',
            'jumlah_anak' => 'integer',
            'lokasi_terpencil' => 'boolean',
        ];
    }

    // ===== Role Checks =====

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKetua(): bool
    {
        return $this->role === 'ketua';
    }

    /**
     * True jika user berperan sebagai atasan langsung (supervisor).
     * Note: ketua & admin juga bisa approve sebagai atasan via canApproveAsAtasan().
     */
    public function isAtasan(): bool
    {
        return in_array($this->role, ['atasan', 'panitera', 'sekretaris']);
    }

    public function isPanitera(): bool
    {
        return $this->role === 'panitera';
    }

    public function isSekretaris(): bool
    {
        return $this->role === 'sekretaris';
    }

    public function isPegawai(): bool
    {
        return $this->role === 'pegawai';
    }

    public function isHakim(): bool
    {
        return $this->role === 'hakim';
    }

    public function isHakimAdHoc(): bool
    {
        return $this->role === 'hakim_ad_hoc';
    }

    public function canApproveAsAtasan(): bool
    {
        return in_array($this->role, ['atasan', 'panitera', 'sekretaris', 'ketua', 'admin']);
    }

    public function canApproveAsPejabat(): bool
    {
        return in_array($this->role, ['ketua', 'admin']);
    }

    public function isKepegawaian(): bool
    {
        return in_array($this->role, ['atasan', 'panitera', 'sekretaris'])
            && in_array($this->unit_kerja, ['kepegawaian', 'Kepegawaian', 'KEPEGAWAIAN']);
    }

    /**
     * Bagian utama pegawai: 'hakim', 'kepaniteraan', atau 'kesekretariatan'.
     * Digunakan untuk kuota cuti bersamaan 30% per bagian.
     */
    public function getBagian(): string
    {
        if ($this->role === 'hakim') {
            return 'hakim';
        }
        if ($this->role === 'hakim_ad_hoc') {
            return 'hakim_ad_hoc';
        }
        if ($this->role === 'panitera') {
            return 'kepaniteraan';
        }
        if ($this->role === 'sekretaris') {
            return 'kesekretariatan';
        }
        // Pegawai — tentukan dari unit_kerja
        $uk = strtolower($this->unit_kerja ?? '');
        if (str_contains($uk, 'panitera') || str_contains($uk, 'kepaniteraan')) {
            return 'kepaniteraan';
        }
        if (str_contains($uk, 'sekretariat') || str_contains($uk, 'subbagian')) {
            return 'kesekretariatan';
        }
        return 'umum';
    }

    // ===== Approval Flow =====

    /**
     * Cuti user ini langsung ke Ketua tanpa review atasan?
     *
     * True jika:
     * - atasan_id user merujuk ke user ber-role ketua, ATAU
     * - user sendiri adalah Ketua/Panitera/Sekretaris
     *
     * Alur Pengadilan:
     * - Hakim/Cakim/Panitera/Sekretaris/Ketua → langsung Ketua (1 level)
     * - Staff Kepaniteraan (atasan=Panitera) → Panitera → Ketua (2 level)
     * - Staff Kesekretariatan (atasan=Sekretaris) → Sekretaris → Ketua (2 level)
     */
    public function skipAtasanReview(): bool
    {
        // Ketua sendiri — langsung ke admin/sistem
        if ($this->isKetua()) {
            return true;
        }

        // Panitera/Sekretaris — atasan mereka adalah Ketua
        if ($this->isPanitera() || $this->isSekretaris()) {
            return true;
        }

        // Pegawai (hakim, cakim, dll) yang atasannya langsung Ketua
        if ($this->atasan_id) {
            $atasan = $this->relationLoaded('atasan')
                ? $this->atasan
                : User::find($this->atasan_id);
            return $atasan && $atasan->isKetua();
        }

        return false;
    }

    // ===== Masa Kerja =====

    public function getMasaKerjaTahunAttribute(): ?float
    {
        if (!$this->masa_kerja_mulai) {
            return null;
        }
        return $this->masa_kerja_mulai->diffInYears(now());
    }

    public function getMasaKerjaFormatAttribute(): ?string
    {
        if (!$this->masa_kerja_mulai) {
            return null;
        }
        $now = now();
        $years = (int) $this->masa_kerja_mulai->diffInYears($now);
        $afterYears = $this->masa_kerja_mulai->copy()->addYears($years);
        $months = (int) $afterYears->diffInMonths($now);
        $afterMonths = $afterYears->copy()->addMonths($months);
        $days = (int) $afterMonths->diffInDays($now);
        return "{$years} tahun {$months} bulan {$days} hari";
    }

    public function sudahBekerjaSatuTahun(): bool
    {
        return $this->masaKerjaTahun !== null && $this->masaKerjaTahun >= 1;
    }

    /**
     * Apakah pegawai ini berhak mengajukan cuti?
     *
     * Semua pegawai berhak KECUALI:
     * - CPNS (belum diangkat penuh)
     * - PPPK yang baru dilantik (masa kerja < 1 tahun)
     */
    public function bolehCuti(): bool
    {
        if ($this->status_pegawai === 'cpns') {
            return false;
        }

        if ($this->status_pegawai === 'pppk' && !$this->sudahBekerjaSatuTahun()) {
            return false;
        }

        return true;
    }

    public function sudahBekerjaLimaTahun(): bool
    {
        return $this->masaKerjaTahun !== null && $this->masaKerjaTahun >= 5;
    }

    // ===== Relationships =====

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function cutiRecords(): HasMany
    {
        return $this->hasMany(CutiRecord::class);
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }

    public function bawahan(): HasMany
    {
        return $this->hasMany(User::class, 'atasan_id');
    }
}
