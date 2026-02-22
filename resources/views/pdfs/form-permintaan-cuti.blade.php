<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: 210mm 330mm;
            margin: 15mm 15mm 12mm 15mm;
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
            line-height: 1.4;
            margin-bottom: 10pt;
        }
        .judul {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 6pt;
            text-transform: uppercase;
        }
        .nomor-surat {
            margin-bottom: 8pt;
            font-size: 9pt;
        }
        table.form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        table.form-table td, table.form-table th {
            border: 1px solid #000;
            padding: 3pt 5pt;
            vertical-align: top;
            font-size: 9pt;
        }
        table.form-table th {
            font-weight: bold;
            text-align: left;
        }
        table.catatan-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.catatan-table td, table.catatan-table th {
            border: 1px solid #000;
            padding: 2pt 4pt;
            vertical-align: middle;
            font-size: 8pt;
            text-align: center;
        }
        table.catatan-table th {
            font-weight: bold;
            font-size: 8pt;
        }
        .section-header {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .checkbox {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9pt;
        }
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
        }
        .ttd-table td {
            border: 1px solid #000;
            padding: 4pt 6pt;
            vertical-align: top;
            font-size: 8.5pt;
            width: 33.33%;
        }
        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }
        .no-border {
            border: none !important;
        }
        .text-center {
            text-align: center;
        }
        .field-label {
            width: 100pt;
        }
        .field-sep {
            width: 8pt;
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

    // Nomor surat
    $bulanRM = $bulanRomawi[$leaveRequest->created_at->month - 1];
    $tahunSurat = $leaveRequest->created_at->year;
    $nomorSurat = "___/PP/OT.01.2/{$bulanRM}/{$tahunSurat}";

    // Terbilang (1-999)
    $satuan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
    $terbilangFn = function($n) use (&$terbilangFn, $satuan) {
        $n = abs((int) $n);
        if ($n < 12) return $satuan[$n];
        if ($n < 20) return $satuan[$n - 10] . ' belas';
        if ($n < 100) return $satuan[(int)($n / 10)] . ' puluh' . ($n % 10 ? ' ' . $satuan[$n % 10] : '');
        if ($n < 200) return 'seratus' . ($n - 100 ? ' ' . $terbilangFn($n - 100) : '');
        if ($n < 1000) return $satuan[(int)($n / 100)] . ' ratus' . ($n % 100 ? ' ' . $terbilangFn($n % 100) : '');
        return (string) $n;
    };
    $terbilang = $terbilangFn($hariKerja);

    // Masa kerja format singkat (XX tahun XX bulan)
    $masaKerjaText = '-';
    if ($user->masa_kerja_mulai) {
        $now = $leaveRequest->created_at ?? now();
        $years = (int) $user->masa_kerja_mulai->diffInYears($now);
        $afterYears = $user->masa_kerja_mulai->copy()->addYears($years);
        $months = (int) $afterYears->diffInMonths($now);
        $masaKerjaText = "{$years} Tahun {$months} Bulan";
    }

    // Jenis cuti mapping
    $jenisCutiList = [
        \App\Models\LeaveRequest::TYPE_TAHUNAN => 'Cuti Tahunan',
        \App\Models\LeaveRequest::TYPE_BESAR => 'Cuti Besar',
        \App\Models\LeaveRequest::TYPE_SAKIT => 'Cuti Sakit',
        \App\Models\LeaveRequest::TYPE_MELAHIRKAN => 'Cuti Melahirkan',
        \App\Models\LeaveRequest::TYPE_ALASAN_PENTING => 'Cuti Karena Alasan Penting',
        \App\Models\LeaveRequest::TYPE_LUAR_TANGGUNGAN => 'Cuti di Luar Tanggungan Negara',
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

    // Alamat selama cuti
    $alamatCuti = $leaveRequest->alamat_cuti ?? $user->alamat ?? '......................................................................';
    $teleponCuti = $leaveRequest->telepon_cuti ?? $user->telepon ?? '.....................';

    // Atasan & pejabat
    $atasan = $leaveRequest->atasanReviewer;
    $pejabat = $leaveRequest->pejabat ?? $ketua;
    $isDirectToKetua = $user->skipAtasanReview();

    // Pertimbangan atasan mapping
    $pertimbanganMap = [
        'setuju' => 'DISETUJUI',
        'ubah' => 'PERUBAHAN',
        'tangguhkan' => 'DITANGGUHKAN',
        'tolak' => 'TIDAK DISETUJUI',
    ];

    // Keputusan pejabat mapping
    $keputusanMap = [
        'setuju' => 'DISETUJUI',
        'ubah' => 'PERUBAHAN',
        'tangguhkan' => 'DITANGGUHKAN',
        'tolak' => 'TIDAK DISETUJUI',
    ];

    // Tahun untuk catatan cuti
    $tahunN = $leaveRequest->created_at->year;
    $tahunN1 = $tahunN - 1;
    $tahunN2 = $tahunN - 2;

    // Jenis cuti label untuk catatan
    $jenisCutiCatatan = [
        'Cuti Tahunan',
        'Cuti Besar',
        'Cuti Sakit',
        'Cuti Melahirkan',
        'Cuti Karena Alasan Penting',
        'Cuti di Luar Tanggungan Negara',
    ];
@endphp

{{-- Header kanan atas --}}
<div class="header-right">
    Anak Lampiran I-b<br>
    Peraturan Badan Kepegawaian Negara<br>
    Republik Indonesia<br>
    Nomor 24 Tahun 2017<br>
    Tanggal 17 Oktober 2017
</div>

{{-- Judul --}}
<div class="judul">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>

{{-- Nomor Surat --}}
<div class="nomor-surat">
    Nomor : {{ $nomorSurat }}
</div>

{{-- TABEL UTAMA --}}
<table class="form-table">
    {{-- I. DATA PEGAWAI --}}
    <tr>
        <td colspan="2" class="section-header">I. DATA PEGAWAI</td>
    </tr>
    <tr>
        <td colspan="2" style="padding: 4pt 5pt;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td class="field-label no-border">Nama</td>
                    <td class="field-sep no-border">:</td>
                    <td class="no-border">{{ $user->name }}</td>
                </tr>
                <tr>
                    <td class="field-label no-border">NIP</td>
                    <td class="field-sep no-border">:</td>
                    <td class="no-border">{{ $user->nip }}</td>
                </tr>
                <tr>
                    <td class="field-label no-border">Jabatan</td>
                    <td class="field-sep no-border">:</td>
                    <td class="no-border">{{ $user->jabatan ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="field-label no-border">Masa Kerja</td>
                    <td class="field-sep no-border">:</td>
                    <td class="no-border">{{ $masaKerjaText }}</td>
                </tr>
                <tr>
                    <td class="field-label no-border">Unit Kerja</td>
                    <td class="field-sep no-border">:</td>
                    <td class="no-border">{{ $user->unit_kerja ?? 'Pengadilan Negeri Natuna' }}</td>
                </tr>
            </table>
        </td>
    </tr>

    {{-- II. JENIS CUTI YANG DIAMBIL --}}
    <tr>
        <td colspan="2" class="section-header">II. JENIS CUTI YANG DIAMBIL **)</td>
    </tr>
    <tr>
        <td colspan="2" style="padding: 4pt 5pt;">
            <table style="width: 100%; border-collapse: collapse;">
                @php $cutiIdx = 0; @endphp
                @foreach($jenisCutiList as $typeKey => $typeLabel)
                    @if($cutiIdx % 2 === 0)<tr>@endif
                    <td class="no-border" style="width: 50%; padding: 1pt 0;">
                        <span class="checkbox">{{ $leaveRequest->type === $typeKey ? '☑' : '☐' }}</span>
                        {{ ($cutiIdx + 1) }}. {{ $typeLabel }}
                    </td>
                    @if($cutiIdx % 2 === 1)</tr>@endif
                    @php $cutiIdx++; @endphp
                @endforeach
                @if($cutiIdx % 2 !== 0)</tr>@endif
            </table>
        </td>
    </tr>

    {{-- III. ALASAN CUTI --}}
    <tr>
        <td colspan="2" class="section-header">III. ALASAN CUTI</td>
    </tr>
    <tr>
        <td colspan="2" style="padding: 4pt 5pt; min-height: 20pt;">
            {{ $alasan }}
        </td>
    </tr>

    {{-- IV. LAMANYA CUTI --}}
    <tr>
        <td colspan="2" class="section-header">IV. LAMANYA CUTI</td>
    </tr>
    <tr>
        <td colspan="2" style="padding: 4pt 5pt;">
            Selama {{ $terbilang }} ({{ $hariKerja }}) hari kerja,<br>
            mulai tanggal {{ $formatTanggal($leaveRequest->start_date) }} s/d {{ $formatTanggal($leaveRequest->end_date) }}
        </td>
    </tr>

    {{-- V. CATATAN CUTI --}}
    <tr>
        <td colspan="2" class="section-header">V. CATATAN CUTI ***)</td>
    </tr>
    <tr>
        <td colspan="2" style="padding: 0;">
            <table class="catatan-table">
                <tr>
                    <th style="width: 25pt;" rowspan="2">No.</th>
                    <th rowspan="2">Jenis Cuti</th>
                    <th colspan="3">Catatan Cuti</th>
                    <th rowspan="2" style="width: 45pt;">Sisa</th>
                    <th rowspan="2" style="width: 60pt;">Keterangan</th>
                </tr>
                <tr>
                    <th style="width: 55pt;">Tahun {{ $tahunN2 }}</th>
                    <th style="width: 55pt;">Tahun {{ $tahunN1 }}</th>
                    <th style="width: 55pt;">Tahun {{ $tahunN }}</th>
                </tr>
                @foreach($jenisCutiCatatan as $idx => $namaJenis)
                    <tr>
                        <td>{{ $idx + 1 }}.</td>
                        <td style="text-align: left; font-size: 7.5pt;">{{ $namaJenis }}</td>
                        @if($namaJenis === 'Cuti Tahunan')
                            {{-- Cuti tahunan: tampilkan data dari CutiRecord --}}
                            <td>{{ isset($catatanCuti[$tahunN2]) ? $catatanCuti[$tahunN2]->cuti_diambil . ' hari' : '-' }}</td>
                            <td>{{ isset($catatanCuti[$tahunN1]) ? $catatanCuti[$tahunN1]->cuti_diambil . ' hari' : '-' }}</td>
                            <td>{{ isset($catatanCuti[$tahunN]) ? $catatanCuti[$tahunN]->cuti_diambil . ' hari' : '-' }}</td>
                            <td>{{ isset($catatanCuti[$tahunN]) ? $catatanCuti[$tahunN]->sisa_cuti . ' hari' : ($user->leave_balance . ' hari') }}</td>
                            <td style="font-size: 7pt;">
                                @if(isset($catatanCuti[$tahunN]) && $catatanCuti[$tahunN]->keterangan)
                                    {{ $catatanCuti[$tahunN]->keterangan }}
                                @else
                                    -
                                @endif
                            </td>
                        @else
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        @endif
                    </tr>
                @endforeach
            </table>
        </td>
    </tr>

    {{-- VI. ALAMAT SELAMA MENJALANKAN CUTI --}}
    <tr>
        <td colspan="2" class="section-header">VI. ALAMAT SELAMA MENJALANKAN CUTI</td>
    </tr>
    <tr>
        <td colspan="2" style="padding: 4pt 5pt; min-height: 24pt;">
            {{ $alamatCuti }}<br>
            Telp. {{ $teleponCuti }}
        </td>
    </tr>
</table>

{{-- TANDA TANGAN (3 kolom) --}}
<table class="ttd-table">
    {{-- Header row --}}
    <tr>
        <td class="text-center" style="font-weight: bold; font-size: 8pt;">
            Hormat saya,
        </td>
        <td class="text-center" style="font-weight: bold; font-size: 8pt;">
            PERTIMBANGAN ATASAN LANGSUNG
        </td>
        <td class="text-center" style="font-weight: bold; font-size: 8pt;">
            KEPUTUSAN PEJABAT YANG BERWENANG<br>MEMBERIKAN CUTI
        </td>
    </tr>

    {{-- Content row --}}
    <tr>
        {{-- Kolom 1: Pemohon --}}
        <td style="height: 120pt;">
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <div style="text-align: center;">
                <span class="ttd-nama">{{ $user->name }}</span><br>
                NIP. {{ $user->nip }}
            </div>
        </td>

        {{-- Kolom 2: Pertimbangan Atasan --}}
        <td style="height: 120pt;">
            @if($isDirectToKetua && !$atasan)
                <div style="text-align: center; padding-top: 20pt; font-style: italic; font-size: 8pt;">
                    LANGSUNG KE PEJABAT<br>BERWENANG
                </div>
            @else
                @php
                    $pertimbanganOptions = ['DISETUJUI', 'PERUBAHAN *)', 'DITANGGUHKAN *)', 'TIDAK DISETUJUI *)'];
                    $pertimbanganValues = ['setuju', 'ubah', 'tangguhkan', 'tolak'];
                @endphp
                @foreach($pertimbanganOptions as $pIdx => $pOption)
                    <span class="checkbox">{{ $leaveRequest->pertimbangan_atasan === $pertimbanganValues[$pIdx] ? '☑' : '☐' }}</span> {{ $pOption }}<br>
                @endforeach
                <br>
                @if($atasan)
                    <div style="text-align: center; margin-top: 10pt;">
                        {{ $formatTanggal($leaveRequest->reviewed_at) }}<br>
                        <br>
                        <span class="ttd-nama">{{ $atasan->name }}</span><br>
                        NIP. {{ $atasan->nip }}
                    </div>
                @else
                    <div style="text-align: center; margin-top: 10pt;">
                        ......................................<br>
                        <br>
                        ......................................<br>
                        NIP. ......................................
                    </div>
                @endif
            @endif
        </td>

        {{-- Kolom 3: Keputusan Pejabat --}}
        <td style="height: 120pt;">
            @php
                $keputusanOptions = ['DISETUJUI', 'PERUBAHAN *)', 'DITANGGUHKAN *)', 'TIDAK DISETUJUI *)'];
                $keputusanValues = ['setuju', 'ubah', 'tangguhkan', 'tolak'];
            @endphp
            @foreach($keputusanOptions as $kIdx => $kOption)
                <span class="checkbox">{{ $leaveRequest->keputusan_pejabat === $keputusanValues[$kIdx] ? '☑' : '☐' }}</span> {{ $kOption }}<br>
            @endforeach
            <br>
            @if($pejabat)
                <div style="text-align: center; margin-top: 10pt;">
                    @if($leaveRequest->decided_at)
                        {{ $formatTanggal($leaveRequest->decided_at) }}<br>
                    @else
                        ......................................<br>
                    @endif
                    <br>
                    <span class="ttd-nama">{{ $pejabat->name }}</span><br>
                    NIP. {{ $pejabat->nip }}
                </div>
            @else
                <div style="text-align: center; margin-top: 10pt;">
                    ......................................<br>
                    <br>
                    ......................................<br>
                    NIP. ......................................
                </div>
            @endif
        </td>
    </tr>
</table>

{{-- Catatan kaki --}}
<div style="font-size: 7pt; margin-top: 6pt; line-height: 1.4;">
    *) Coret yang tidak perlu<br>
    **) Pilih salah satu jenis cuti yang diambil, beri tanda centang (✓) pada kotak yang disediakan<br>
    ***) Diisi oleh pejabat yang menangani bidang kepegawaian
</div>

</body>
</html>
