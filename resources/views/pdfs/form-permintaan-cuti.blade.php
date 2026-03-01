<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: legal;
            margin: 15mm 20mm 15mm 20mm;
        }
        body {
            font-family: 'Bookman Old Style', 'URW Bookman L', 'Bookman', 'Georgia', serif;
            font-size: 9pt;
            color: #000;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }
        .header-right {
            text-align: right;
            font-size: 8pt;
            line-height: 1.2;
            margin-bottom: 8pt;
            font-weight: normal;
        }
        .header-right div:first-child {
            text-decoration: underline;
            font-weight: bold;
        }
        .surat-header {
            margin-bottom: 8pt;
            font-size: 9pt;
        }
        .surat-date {
            text-align: right;
            margin-bottom: 3pt;
        }
        .surat-kepada {
            margin-bottom: 3pt;
        }
        .judul {
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 2pt;
            text-decoration: underline;
        }
        .nomor-surat {
            text-align: center;
            font-weight: bold;
            margin-bottom: 8pt;
            font-size: 9pt;
        }

        /* Tabel utama */
        table.form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6pt;
        }
        table.form-table td, table.form-table th {
            border: 1px solid #000;
            padding: 2pt 4pt;
            vertical-align: top;
            font-size: 9pt;
            line-height: 1.2;
        }
        table.form-table th {
            font-weight: bold;
            text-align: left;
        }

        /* Section headers */
        .section-header {
            font-weight: bold;
            background-color: transparent;
            text-align: left;
        }

        /* Data pegawai table */
        table.data-pegawai {
            width: 100%;
            border-collapse: collapse;
        }
        table.data-pegawai td {
            border: 1px solid #000;
            padding: 2pt 4pt;
            vertical-align: top;
            font-size: 9pt;
            line-height: 1.2;
        }
        table.data-pegawai .label-cell {
            width: 60pt;
            font-weight: bold;
        }
        table.data-pegawai .value-cell {
            width: auto;
        }

        /* Jenis cuti */
        .checkbox {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
        }

        /* Catatan cuti table */
        table.catatan-cuti {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
        }
        table.catatan-cuti td {
            border: 1px solid #000;
            padding: 2pt 3pt;
            font-size: 8pt;
            text-align: center;
            vertical-align: middle;
            line-height: 1.2;
        }
        table.catatan-cuti .header-col {
            font-weight: bold;
        }

        /* Alamat section */
        .alamat-section {
            padding: 4pt;
            min-height: 70pt;
            position: relative;
        }
        .ttd-pemohon {
            position: absolute;
            right: 10pt;
            bottom: 8pt;
            text-align: center;
            width: 150pt;
            font-size: 9pt;
        }
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 40pt;
        }

        /* Pertimbangan & Keputusan */
        .approval-section {
            padding: 4pt;
            min-height: 100pt;
            position: relative;
        }
        .approval-options {
            margin-bottom: 8pt;
            font-size: 9pt;
        }
        .ttd-approval {
            position: absolute;
            right: 10pt;
            bottom: 8pt;
            text-align: center;
            width: 150pt;
            font-size: 9pt;
        }

        /* Catatan kaki */
        .footer-notes {
            font-size: 8pt;
            margin-top: 4pt;
            line-height: 1.2;
        }

        .no-border {
            border: none !important;
        }
    </style>
</head>
<body>

@php
    $user = $leaveRequest->user;
    $hariKerja = $leaveRequest->total_hari_kerja ?? $leaveRequest->total_days;

    // Bulan Indonesia
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    // Bulan Romawi
    $bulanRomawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];

    // Format tanggal Indonesia
    $formatTanggal = function($date) use ($bulanIndo) {
        if (!$date) return '...';
        return $date->day . ' ' . $bulanIndo[$date->month] . ' ' . $date->year;
    };

    // Nomor surat dengan format xxx/KPN.W32.04/KP5.3/[Bulan]/[Tahun]
    $bulanRM = $bulanRomawi[$leaveRequest->created_at->month - 1];
    $tahunSurat = $leaveRequest->created_at->year;
    // Format: 700/KPN.W32.04/KP5.3/XII/2025
    $nomorSurat = "700/KPN.W32.04/KP5.3/{$bulanRM}/{$tahunSurat}";

    // Masa kerja
    $masaKerjaText = '0 Tahun 0 Bulan';
    if ($user->masa_kerja_mulai) {
        $now = $leaveRequest->created_at ?? now();
        $years = (int) $user->masa_kerja_mulai->diffInYears($now);
        $afterYears = $user->masa_kerja_mulai->copy()->addYears($years);
        $months = (int) $afterYears->diffInMonths($now);
        $masaKerjaText = "{$years} Tahun {$months} Bulan";
    }

    // Jenis cuti mapping
    $jenisCutiOptions = [
        [\App\Models\LeaveRequest::TYPE_TAHUNAN, '1. Cuti Tahunan'],
        [\App\Models\LeaveRequest::TYPE_BESAR, '2. Cuti Besar'],
        [\App\Models\LeaveRequest::TYPE_SAKIT, '3. Cuti Sakit'],
        [\App\Models\LeaveRequest::TYPE_MELAHIRKAN, '4. Cuti Melahirkan'],
        [\App\Models\LeaveRequest::TYPE_ALASAN_PENTING, '5. Cuti Karena Alasan Penting'],
        [\App\Models\LeaveRequest::TYPE_LUAR_TANGGUNGAN, '6. Cuti di Luar Tanggungan Negara'],
    ];

    // Alasan cuti
    $alasan = $leaveRequest->reason;
    if ($leaveRequest->type === \App\Models\LeaveRequest::TYPE_ALASAN_PENTING && $leaveRequest->alasan_cap) {
        $capLabel = \App\Models\LeaveRequest::capLabels()[$leaveRequest->alasan_cap] ?? '';
        if ($capLabel) {
            $alasan .= ' (' . strtolower($capLabel) . ')';
        }
    }
    if ($leaveRequest->type === \App\Models\LeaveRequest::TYPE_MELAHIRKAN && $leaveRequest->kelahiran_ke) {
        $alasan .= ' (kelahiran anak ke-' . $leaveRequest->kelahiran_ke . ')';
    }

    // Alamat & telepon
    $alamatCuti = $leaveRequest->alamat_cuti ?? $user->alamat ?? '';
    $teleponCuti = $leaveRequest->telepon_cuti ?? $user->telepon ?? '';

    // Atasan & pejabat
    $atasan = $leaveRequest->atasanReviewer;
    $pejabat = $leaveRequest->pejabat ?? $ketua;
    $isDirectToKetua = $user->skipAtasanReview();

    // Catatan cuti tahun berjalan
    $tahunSekarang = $leaveRequest->created_at->year;
    $cutiRecord = \App\Models\CutiRecord::where('user_id', $user->id)
        ->where('tahun', $tahunSekarang)
        ->first();

    // Cascade: carry-over dari tahun lalu dulu, baru potong tahun ini
    $hariCuti    = $leaveRequest->total_hari_kerja ?? $leaveRequest->total_days ?? 0;
    $carryN1     = ($cutiRecord && $cutiRecord->carry_over > 0) ? (int)$cutiRecord->carry_over : 0;
    $hakN        = $cutiRecord ? (int)$cutiRecord->hak_cuti : 12;
    $deductN1    = min($carryN1, $hariCuti);
    $deductN     = max(0, $hariCuti - $deductN1);
    $sisaN1Sisa  = $carryN1 - $deductN1;       // sisa carry-over setelah cuti ini
    $sisaCuti    = max(0, $hakN - $deductN);    // sisa tahun ini setelah cuti ini
@endphp

{{-- Header kanan atas --}}
<div class="header-right">
    <div>LAMPIRAN II</div>
    SURAT EDARAN SEKRETARIS MAHKAMAH AGUNG<br>
    REPUBLIK INDONESIA NOMOR 13 TAHUN 2019
</div>

{{-- Surat header --}}
<div class="surat-header">
    <div class="surat-date">Ranai, {{ $formatTanggal($leaveRequest->created_at) }}</div>
    <div class="surat-kepada">
        Kepada Yth.<br>
        Ketua Pengadilan Negeri Natuna<br>
        Di-<br>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ranai
    </div>
</div>

{{-- Judul --}}
<div class="judul">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>

{{-- Nomor Surat --}}
<div class="nomor-surat">NOMOR: {{ $nomorSurat }}</div>

{{-- I. DATA PEGAWAI --}}
<table class="data-pegawai">
    <tr>
        <td colspan="4" class="section-header">I. DATA PEGAWAI</td>
    </tr>
    <tr>
        <td class="label-cell">Nama</td>
        <td class="value-cell">{{ $user->name }}</td>
        <td class="label-cell">NIP</td>
        <td class="value-cell">{{ $user->nip }}</td>
    </tr>
    <tr>
        <td class="label-cell">Jabatan</td>
        <td class="value-cell">{{ $user->jabatan ?? '-' }}</td>
        <td class="label-cell">Masa Kerja</td>
        <td class="value-cell">{{ $masaKerjaText }}</td>
    </tr>
    <tr>
        <td class="label-cell">Unit Kerja</td>
        <td colspan="3">{{ $user->unit_kerja ?? 'Pengadilan Negeri Natuna' }}</td>
    </tr>
</table>

{{-- II. JENIS CUTI YANG DIAMBIL --}}
<table class="form-table">
    <tr>
        <td colspan="2" class="section-header">II. JENIS CUTI YANG DIAMBIL *</td>
    </tr>
    <tr>
        @foreach($jenisCutiOptions as $idx => $option)
            @if($idx % 2 === 0 && $idx > 0)</tr><tr>@endif
            <td style="width: 50%; padding: 2pt 4pt;">
                <span class="checkbox">{{ $leaveRequest->type === $option[0] ? '√' : '-' }}</span>
                &nbsp;&nbsp;{{ $option[1] }}
            </td>
        @endforeach
    </tr>
</table>

{{-- III. ALASAN CUTI --}}
<table class="form-table">
    <tr>
        <td class="section-header">III. ALASAN CUTI</td>
    </tr>
    <tr>
        <td style="padding: 4pt;">{{ $alasan }}</td>
    </tr>
</table>

{{-- IV. LAMANYA CUTI --}}
<table class="form-table">
    <tr>
        <td class="section-header">IV. LAMANYA CUTI</td>
    </tr>
    <tr>
        <td style="padding: 4pt;">
            Selama <u>&nbsp;&nbsp;{{ $hariKerja }}&nbsp;&nbsp;</u> Hari/<del>bulan</del>/<del>tahun</del>**
            &nbsp;&nbsp;|&nbsp;&nbsp;
            Mulai Tanggal <u>&nbsp;&nbsp;{{ $formatTanggal($leaveRequest->start_date) }}&nbsp;&nbsp;</u>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            s/d <u>&nbsp;&nbsp;{{ $formatTanggal($leaveRequest->end_date) }}&nbsp;&nbsp;</u>
        </td>
    </tr>
</table>

{{-- V. CATATAN CUTI --}}
<table class="form-table">
    <tr>
        <td class="section-header">V. CATATAN CUTI ***</td>
    </tr>
    <tr>
        <td style="padding: 0;">
            <table class="catatan-cuti">
                <tr>
                    <td class="header-col" style="width: 15%;">CUTI TAHUNAN</td>
                    <td class="header-col" style="width: 20%;">PAFAF PETUGAS CUTI</td>
                    <td class="header-col" style="width: 15%;">CUTI BESAR</td>
                    <td class="header-col" style="width: 50%;"></td>
                </tr>
                @php
                    $catatanRows = [
                        [$tahunSekarang - 2, 0, 'Sisa 0', 'CUTI MELAHIRKAN', '-'],
                        [$tahunSekarang - 1, $carryN1, 'Sisa ' . $sisaN1Sisa, 'CUTI KARENA ALASAN PENTING', '-'],
                        [$tahunSekarang, $hakN, 'Sisa ' . $sisaCuti, 'CUTI DILUAR TANGGUNGAN NEGARA', '-'],
                    ];
                @endphp
                @foreach($catatanRows as $idx => $row)
                    <tr>
                        <td style="text-align: left;">{{ $row[0] }}</td>
                        <td style="text-align: left;">{{ $row[1] }}</td>
                        <td style="text-align: left;">{{ $row[2] }}</td>
                        @if($idx === 0)
                            <td style="text-align: left;">{{ $row[3] }}</td>
                        @else
                            <td style="text-align: left;">{{ $row[3] }}</td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>
</table>

{{-- VI. ALAMAT SELAMA MENJALANKAN CUTI --}}
<table class="form-table">
    <tr>
        <td class="section-header">VI. ALAMAT SELAMA MENJALANKAN CUTI</td>
    </tr>
    <tr>
        <td class="alamat-section">
            {{ $alamatCuti }}<br>
            @if($teleponCuti)
                TELP. {{ $teleponCuti }}
            @endif

            <div class="ttd-pemohon">
                Hormat Saya,<br>
                <div class="ttd-nama">{{ $user->name }}</div>
                NIP. {{ $user->nip }}
            </div>
        </td>
    </tr>
</table>

{{-- VII. PERTIMBANGAN ATASAN LANGSUNG --}}
<table class="form-table">
    <tr>
        <td class="section-header">VII. PERTIMBANGAN ATASAN LANGSUNG **</td>
    </tr>
    <tr>
        <td class="approval-section">
            @if($isDirectToKetua && !$atasan)
                <div style="text-align: center; padding-top: 25pt; font-style: italic; font-size: 8pt;">
                    LANGSUNG KE PEJABAT BERWENANG
                </div>
            @else
                <div class="approval-options">
                    <span class="checkbox">{{ $leaveRequest->pertimbangan_atasan === 'setuju' ? '√' : '' }}</span> DISETUJUI
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="checkbox">{{ $leaveRequest->pertimbangan_atasan === 'ubah' ? '√' : '' }}</span> PERUBAHAN****
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="checkbox">{{ $leaveRequest->pertimbangan_atasan === 'tangguhkan' ? '√' : '' }}</span> DITANGGUHKAN****
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <span class="checkbox">{{ $leaveRequest->pertimbangan_atasan === 'tolak' ? '√' : '' }}</span> TIDAK DISETUJUI ****
                </div>

                @if($atasan && $leaveRequest->reviewed_at)
                    <div class="ttd-approval">
                        Sekretaris Pengadilan Negeri Natuna,<br>
                        <br><br><br>
                        <div class="ttd-nama">{{ $atasan->name }}</div>
                        NIP. {{ $atasan->nip }}
                    </div>
                @endif
            @endif
        </td>
    </tr>
</table>

{{-- VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI --}}
<table class="form-table">
    <tr>
        <td class="section-header">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**</td>
    </tr>
    <tr>
        <td class="approval-section">
            <div class="approval-options">
                <span class="checkbox">{{ $leaveRequest->keputusan_pejabat === 'setuju' ? '√' : '' }}</span> DISETUJUI
                &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="checkbox">{{ $leaveRequest->keputusan_pejabat === 'ubah' ? '√' : '' }}</span> PERUBAHAN****
                &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="checkbox">{{ $leaveRequest->keputusan_pejabat === 'tangguhkan' ? '√' : '' }}</span> DITANGGUHKAN****
                &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="checkbox">{{ $leaveRequest->keputusan_pejabat === 'tolak' ? '√' : '' }}</span> TIDAK DISETUJUI ****
            </div>

            @if($pejabat && $leaveRequest->decided_at)
                <div class="ttd-approval">
                    Ketua Pengadilan Negeri Natuna,<br>
                    <br><br><br>
                    <div class="ttd-nama">{{ $pejabat->name }}</div>
                    NIP. {{ $pejabat->nip }}
                </div>
            @endif
        </td>
    </tr>
</table>

{{-- Catatan kaki --}}
<div class="footer-notes">
    <strong>Catatan:</strong><br>
    * &nbsp;&nbsp;&nbsp;&nbsp; Coret yang tidak perlu.<br>
    ** &nbsp;&nbsp; Pilih salah satu dengan memberi tanda centang (√).<br>
    *** &nbsp; Dalam hal permintaan cuti karena alasan penting yang bersangkutan harus menyerahkan cuti<br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; tahunan terlebih dahulu.<br>
    **** dibubuhi tanda setang dan ditandai.<br>
    P-1 &nbsp;&nbsp;= Cuti tahun berjalan.<br>
    P-2 &nbsp;&nbsp;= Cuti tahun sebelumnya.<br>
    P+2 &nbsp;= Sisa cuti 2 tahun sebelumnya.
</div>

</body>
</html>
