<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'user_id', 'type',
        'start_date', 'end_date', 'reason',
        'alamat_cuti', 'telepon_cuti',
        'alasan_cap', 'kelahiran_ke',
        'dokumen_pendukung', 'total_hari_kerja',
        'status', 'admin_note',
        'atasan_reviewer_id', 'pertimbangan_atasan', 'catatan_atasan', 'reviewed_at',
        'pejabat_id', 'keputusan_pejabat', 'catatan_pejabat', 'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'reviewed_at' => 'datetime',
            'decided_at' => 'datetime',
            'kelahiran_ke' => 'integer',
            'total_hari_kerja' => 'integer',
        ];
    }

    // ===== Constants =====

    const TYPE_TAHUNAN = 'cuti_tahunan';
    const TYPE_BESAR = 'cuti_besar';
    const TYPE_SAKIT = 'cuti_sakit';
    const TYPE_MELAHIRKAN = 'cuti_melahirkan';
    const TYPE_ALASAN_PENTING = 'cuti_alasan_penting';
    const TYPE_LUAR_TANGGUNGAN = 'cuti_luar_tanggungan';
    const TYPE_BERSAMA = 'cuti_bersama'; // SEMA 13/2019: auto-deduct dari cuti tahunan

    // Batas SEMA 13/2019
    const MAX_CONSECUTIVE_DAYS_WITHOUT_APPROVAL = 5; // Maks 5 hari berturut tanpa persetujuan khusus
    const CARRY_OVER_LIMIT = 6; // Maks 6 hari carry-over per tahun sebelumnya
    const MAX_CLTN_YEARS = 3; // Maks 3 tahun CLTN
    const MAX_CLTN_EXTENSION_YEARS = 1; // Perpanjangan CLTN maks 1 tahun
    const CUTI_SAKIT_SURAT_DOKTER_PEMERINTAH_DAYS = 14; // >14 hari wajib surat dokter pemerintah

    const STATUS_DIAJUKAN = 'diajukan';
    const STATUS_PERTIMBANGAN = 'pertimbangan_atasan';
    const STATUS_DISETUJUI = 'disetujui';
    const STATUS_DIUBAH = 'diubah';
    const STATUS_DITANGGUHKAN = 'ditangguhkan';
    const STATUS_DITOLAK = 'ditolak';

    // Backward compat
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const CAP_KELUARGA_SAKIT = 'keluarga_sakit_meninggal';
    const CAP_URUS_HAK = 'urus_hak_keluarga';
    const CAP_PERKAWINAN = 'perkawinan';
    const CAP_SAKIT_KERAS = 'sakit_keras_rawat_inap';
    const CAP_ISTRI_MELAHIRKAN = 'istri_melahirkan';
    const CAP_MUSIBAH = 'musibah_bencana';

    public static function typeLabels(): array
    {
        return [
            self::TYPE_TAHUNAN => 'Cuti Tahunan',
            self::TYPE_BESAR => 'Cuti Besar',
            self::TYPE_SAKIT => 'Cuti Sakit',
            self::TYPE_MELAHIRKAN => 'Cuti Melahirkan',
            self::TYPE_ALASAN_PENTING => 'Cuti Karena Alasan Penting',
            self::TYPE_LUAR_TANGGUNGAN => 'Cuti di Luar Tanggungan Negara',
            self::TYPE_BERSAMA => 'Cuti Bersama',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DIAJUKAN => 'Diajukan',
            self::STATUS_PERTIMBANGAN => 'Pertimbangan Atasan',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_DIUBAH => 'Diubah',
            self::STATUS_DITANGGUHKAN => 'Ditangguhkan',
            self::STATUS_DITOLAK => 'Ditolak',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
        ];
    }

    public static function capLabels(): array
    {
        return [
            self::CAP_KELUARGA_SAKIT => 'Keluarga sakit keras / meninggal dunia',
            self::CAP_URUS_HAK => 'Keluarga meninggal & urus hak-hak',
            self::CAP_PERKAWINAN => 'Melangsungkan perkawinan',
            self::CAP_SAKIT_KERAS => 'Sakit keras (rawat inap)',
            self::CAP_ISTRI_MELAHIRKAN => 'Istri melahirkan / operasi caesar',
            self::CAP_MUSIBAH => 'Musibah kebakaran / bencana alam',
        ];
    }

    // ===== Accessors =====

    public function getTotalDaysAttribute(): int
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date) + 1;
        }
        return 0;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    // ===== Status Checks =====

    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_DIAJUKAN, self::STATUS_PERTIMBANGAN]);
    }

    public function isApproved(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_DISETUJUI]);
    }

    public function isRejected(): bool
    {
        return in_array($this->status, [self::STATUS_REJECTED, self::STATUS_DITOLAK]);
    }

    public function needsAtasanReview(): bool
    {
        return $this->status === self::STATUS_DIAJUKAN;
    }

    public function needsPejabatDecision(): bool
    {
        return $this->status === self::STATUS_PERTIMBANGAN;
    }

    // ===== Relationships =====

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function atasanReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_reviewer_id');
    }

    public function pejabat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pejabat_id');
    }

    /**
     * Get amendments for this leave request
     */
    public function amendments()
    {
        return $this->hasMany(LeaveAmendment::class);
    }

    /**
     * Get appeals for this leave request
     */
    public function appeals()
    {
        return $this->hasMany(LeaveAppeal::class);
    }
}
