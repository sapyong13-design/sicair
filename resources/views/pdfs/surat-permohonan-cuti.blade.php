<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 20mm 20mm 20mm 25mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #000;
            margin: 0;
            padding: 0;
            line-height: 1.8;
        }
        .tanggal {
            text-align: right;
            margin-bottom: 24pt;
        }
        .tujuan {
            margin-bottom: 18pt;
        }
        .tujuan .indent {
            padding-left: 40pt;
        }
        .perihal {
            margin-bottom: 18pt;
        }
        .isi {
            text-align: justify;
        }
        .isi .indent {
            padding-left: 40pt;
        }
        table.data-pemohon {
            margin-left: 0;
            margin-bottom: 12pt;
            border-collapse: collapse;
        }
        table.data-pemohon td {
            padding: 1pt 6pt 1pt 0;
            vertical-align: top;
        }
        table.data-pemohon td.label {
            width: 160pt;
            white-space: nowrap;
        }
        table.data-pemohon td.sep {
            width: 12pt;
        }
        .ttd {
            margin-top: 30pt;
            text-align: right;
        }
        .ttd-box {
            display: inline-block;
            text-align: center;
            width: 220pt;
        }
        .ttd-nama {
            margin-top: 70pt;
            font-weight: bold;
            text-decoration: underline;
        }
        .ttd-nip {
            font-size: 11pt;
        }
    </style>
</head>
<body>

@php
    $user = $leaveRequest->user;
    $hariKerja = $leaveRequest->total_hari_kerja ?? $leaveRequest->total_days;

    // Format tanggal Indonesia
    $bulanIndo = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    $formatTanggal = function($date) use ($bulanIndo) {
        return $date->day . ' ' . $bulanIndo[$date->month] . ' ' . $date->year;
    };

    $tanggalSurat = $formatTanggal($leaveRequest->created_at);
    $tanggalMulai = $formatTanggal($leaveRequest->start_date);
    $tanggalSelesai = $formatTanggal($leaveRequest->end_date);

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

    // Sapaan Ketua
    $sapaanKetua = ($ketua && $ketua->jenis_kelamin === 'P') ? 'Ibu' : 'Bapak';

    // Alasan lengkap (menyambung)
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
@endphp

{{-- Tanggal surat --}}
<div class="tanggal">Natuna, {{ $tanggalSurat }}</div>

{{-- Tujuan surat --}}
<div class="tujuan">
    Kepada Yth.<br>
    {{ $sapaanKetua }} Ketua Pengadilan Negeri Natuna<br>
    Di-<br>
    <div class="indent">Ranai</div>
</div>

{{-- Perihal --}}
<div class="perihal">
    Perihal : <strong>Permohonan {{ $leaveRequest->type_label }}</strong>
</div>

{{-- Isi surat --}}
<div class="isi">
    Dengan hormat,
    <br><br>

    Yang bertanda tangan di bawah ini:

    <br>

    <table class="data-pemohon">
        <tr>
            <td class="label">Nama</td>
            <td class="sep">:</td>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <td class="label">NIP</td>
            <td class="sep">:</td>
            <td>{{ $user->nip }}</td>
        </tr>
        <tr>
            <td class="label">Pangkat/Gol. Ruang</td>
            <td class="sep">:</td>
            <td>{{ $user->golongan_ruang ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td class="sep">:</td>
            <td>{{ $user->jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Satuan Organisasi</td>
            <td class="sep">:</td>
            <td>{{ $user->unit_kerja ?? 'Pengadilan Negeri Natuna' }}</td>
        </tr>
    </table>

    Dengan ini mengajukan permohonan {{ $leaveRequest->type_label }} selama {{ $terbilang }} ({{ $hariKerja }}) hari kerja, terhitung mulai tanggal {{ $tanggalMulai }} sampai dengan tanggal {{ $tanggalSelesai }}, dikarenakan {{ $alasan }}.

    <br><br>

    Demikian permohonan ini saya buat untuk dapat dipertimbangkan sebagaimana mestinya.
</div>

{{-- Tanda tangan --}}
<div class="ttd">
    <div class="ttd-box">
        Hormat saya,
        <div class="ttd-nama">{{ $user->name }}</div>
    </div>
</div>

</body>
</html>
