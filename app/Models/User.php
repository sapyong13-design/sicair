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
        'atasan_id', 'telepon', 'alamat', 'notification_preferences',
        'photo', 'is_active', 'last_login_at',
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
            'notification_preferences' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * #48: Check if user wants to receive a particular email notification.
     * Defaults to true for all if no preference saved.
     */
    public function wantsEmailNotification(string $key): bool
    {
        $prefs = $this->notification_preferences ?? [];
        return $prefs[$key] ?? true;
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
    // Fix #5: Wakil Ketua juga bisa review bawahan sebagai atasan
    public function isAtasan(): bool
    {
        return in_array($this->role, ['atasan', 'panitera', 'sekretaris', 'wakil_ketua']);
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

    public function isWakilKetua(): bool
    {
        return $this->role === 'wakil_ketua';
    }

    // Fix #2: Wakil Ketua juga bisa approve sebagai atasan
    public function canApproveAsAtasan(): bool
    {
        return in_array($this->role, ['atasan', 'panitera', 'sekretaris', 'ketua', 'wakil_ketua', 'admin']);
    }

    // Fix #3: Wakil Ketua bisa menjadi PYB sementara saat Ketua berhalangan
    public function canApproveAsPejabat(): bool
    {
        return in_array($this->role, ['ketua', 'wakil_ketua', 'admin']);
    }

    public function isKepegawaian(): bool
    {
        return in_array($this->role, ['atasan', 'panitera', 'sekretaris'])
            && strtolower(trim($this->unit_kerja ?? '')) === 'kepegawaian';
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

        // Fix #1: Wakil Ketua juga skip langsung ke Ketua
        if ($this->isWakilKetua()) {
            return true;
        }

        // Panitera/Sekretaris — atasan mereka adalah Ketua
        if ($this->isPanitera() || $this->isSekretaris()) {
            return true;
        }

        // Hakim dan Hakim Ad Hoc — langsung ke Ketua sesuai SEMA 13/2019
        if ($this->isHakim() || $this->isHakimAdHoc()) {
            return true;
        }

        // Fix #7: Pegawai yang atasannya langsung Ketua atau Wakil Ketua
        if ($this->atasan_id) {
            $atasan = $this->relationLoaded('atasan')
                ? $this->atasan
                : User::find($this->atasan_id);
            return $atasan && ($atasan->isKetua() || $atasan->isWakilKetua());
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
     * Fix #4: CPNS BOLEH cuti sakit/melahirkan/alasan penting (SEMA 13/2019)
     * Parameter $jenisCuti opsional untuk pengecekan type-aware.
     */
    public function bolehCuti(string $jenisCuti = null): bool
    {
        // Fix #4: CPNS boleh cuti sakit, melahirkan, alasan penting
        if ($this->status_pegawai === 'cpns') {
            if ($jenisCuti === null) {
                // Tanpa jenis: cek umum — CPNS punya hak terbatas
                return false;
            }
            return in_array($jenisCuti, [
                LeaveRequest::TYPE_SAKIT,
                LeaveRequest::TYPE_ALASAN_PENTING,
                LeaveRequest::TYPE_MELAHIRKAN,
            ]);
        }

        if ($this->status_pegawai === 'pppk' && !$this->sudahBekerjaSatuTahun()) {
            if ($jenisCuti === null) {
                return false;
            }
            return in_array($jenisCuti, [
                LeaveRequest::TYPE_SAKIT,
                LeaveRequest::TYPE_MELAHIRKAN,
            ]);
        }

        return true;
    }

    public function sudahBekerjaLimaTahun(): bool
    {
        return $this->masaKerjaTahun !== null && $this->masaKerjaTahun >= 5;
    }

    // Fix #6: Accessor PYB (Pejabat Yang Berwenang) sesuai SEMA 13/2019
    public function getPejabatYangBerwenangAttribute(): string
    {
        if ($this->isKetua() || $this->isWakilKetua()) {
            return 'Ketua Pengadilan Tinggi';
        }
        return 'Ketua Pengadilan Negeri Natuna';
    }

    // Fix #8: Label status pegawai untuk display
    public function getStatusPegawaiLabelAttribute(): string
    {
        return match($this->status_pegawai) {
            'pns' => 'PNS',
            'cpns' => 'CPNS',
            'pppk' => 'PPPK',
            'cakim' => 'Calon Hakim',
            'hakim' => 'Hakim',
            'aparatur' => 'Aparatur',
            default => strtoupper($this->status_pegawai ?? '-'),
        };
    }

    // Fix #9: Label jenis kelamin untuk display
    public function getJenisKelaminLabelAttribute(): string
    {
        return match($this->jenis_kelamin) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
        };
    }

    // Fix #10: Cek apakah pegawai aktif (bukan CPNS/cakim)
    public function isAparaturAktif(): bool
    {
        return in_array($this->status_pegawai, ['pns', 'pppk', 'hakim', 'aparatur']);
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
