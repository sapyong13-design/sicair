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
        'name', 'nip', 'password', 'role', 'leave_balance',
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
        $years = $this->masa_kerja_mulai->diffInYears(now());
        $months = $this->masa_kerja_mulai->diffInMonths(now()) % 12;
        return "{$years} tahun {$months} bulan";
    }

    public function sudahBekerjaSatuTahun(): bool
    {
        return $this->masaKerjaTahun !== null && $this->masaKerjaTahun >= 1;
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
