<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #222;
            margin: 0;
            padding: 0;
        }
        .page {
            padding: 14px 18px;
            page-break-after: always;
        }
        .page:last-child {
            page-break-after: avoid;
        }
        .lembar-label {
            display: inline-block;
            background: #7c3aed;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            padding: 2px 10px;
            border-radius: 10px;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .kop {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .kop h1 {
            margin: 4px 0 2px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .kop .instansi {
            font-size: 11px;
            font-weight: bold;
            margin: 0;
        }
        .kop .sub-instansi {
            font-size: 10px;
            color: #555;
            margin: 1px 0;
        }
        .judul-form {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 10px 0 12px;
            text-decoration: underline;
        }
        table.detail {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.detail td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 11px;
        }
        table.detail td.label {
            width: 38%;
            color: #555;
            font-weight: 600;
        }
        table.detail td.sep {
            width: 4%;
            color: #555;
        }
        .section-title {
            font-weight: bold;
            font-size: 11px;
            background: #f3f0ff;
            color: #5b21b6;
            padding: 4px 8px;
            border-left: 3px solid #7c3aed;
            margin: 10px 0 4px;
        }
        .status-box {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
        }
        .status-disetujui { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .status-ditolak { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
        .status-ditangguhkan { background: #fef3c7; color: #d97706; border: 1px solid #fcd34d; }
        .status-diubah { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
        .status-default { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
        .ttd-area {
            margin-top: 18px;
            display: flex;
            justify-content: space-between;
        }
        .ttd-box {
            width: 30%;
            text-align: center;
        }
        .ttd-box .ttd-title {
            font-size: 10px;
            color: #555;
            margin-bottom: 2px;
        }
        .ttd-box .ttd-name {
            font-size: 10px;
            font-weight: bold;
            margin-top: 52px;
            border-top: 1px solid #333;
            padding-top: 3px;
        }
        .ttd-box .ttd-nip {
            font-size: 9px;
            color: #777;
        }
        .footer-note {
            margin-top: 12px;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }
        .pppk-badge {
            display: inline-block;
            background: #ede9fe;
            color: #7c3aed;
            font-size: 9px;
            font-weight: bold;
            padding: 1px 7px;
            border-radius: 8px;
            margin-left: 4px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

@php
    $lembarList = [
        ['no' => 1, 'untuk' => 'Untuk Pegawai'],
        ['no' => 2, 'untuk' => 'Untuk Sekretaris'],
        ['no' => 3, 'untuk' => 'Untuk Ketua Pengadilan Negeri Natuna'],
    ];

    $statusClass = match(true) {
        $leaveRequest->isApproved() => 'status-disetujui',
        $leaveRequest->isRejected() => 'status-ditolak',
        $leaveRequest->status === 'ditangguhkan' => 'status-ditangguhkan',
        $leaveRequest->status === 'diubah' => 'status-diubah',
        default => 'status-default',
    };
    $statusText = $leaveRequest->status_label ?? ucfirst($leaveRequest->status);

    $sekretaris = $leaveRequest->atasanReviewer;
    $ketua = $leaveRequest->pejabat;
@endphp

@foreach($lembarList as $lembar)
<div class="page">
    {{-- Lembar label --}}
    <div class="lembar-label">LEMBAR {{ $lembar['no'] }} &mdash; {{ strtoupper($lembar['untuk']) }}</div>

    {{-- Kop Surat --}}
    <div class="kop">
        <p class="instansi">MAHKAMAH AGUNG REPUBLIK INDONESIA</p>
        <h1>Pengadilan Negeri Natuna</h1>
        <p class="sub-instansi">{{ $leaveRequest->user->unit_kerja ?? 'Pengadilan Negeri Natuna' }}</p>
    </div>

    <div class="judul-form">Surat Izin Cuti &mdash; Pegawai PPPK</div>

    {{-- Data Pemohon --}}
    <div class="section-title">Data Pemohon</div>
    <table class="detail">
        <tr>
            <td class="label">Nama</td>
            <td class="sep">:</td>
            <td>{{ $leaveRequest->user->name }} <span class="pppk-badge">PPPK</span></td>
        </tr>
        <tr>
            <td class="label">NIP / No. Kontrak</td>
            <td class="sep">:</td>
            <td>{{ $leaveRequest->user->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jabatan</td>
            <td class="sep">:</td>
            <td>{{ $leaveRequest->user->jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Golongan / Ruang</td>
            <td class="sep">:</td>
            <td>{{ $leaveRequest->user->golongan_ruang ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Unit Kerja</td>
            <td class="sep">:</td>
            <td>{{ $leaveRequest->user->unit_kerja ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Masa Kerja (TMT)</td>
            <td class="sep">:</td>
            <td>{{ $leaveRequest->user->masa_kerja_mulai ? $leaveRequest->user->masa_kerja_mulai->format('d M Y') : '-' }}</td>
        </tr>
    </table>

    {{-- Detail Cuti --}}
    <div class="section-title">Detail Cuti</div>
    <table class="detail">
        <tr>
            <td class="label">Jenis Cuti</td>
            <td class="sep">:</td>
            <td>{{ $leaveRequest->type_label }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Mulai</td>
            <td class="sep">:</td>
            <td>{{ $leaveRequest->start_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Selesai</td>
            <td class="sep">:</td>
            <td>{{ $leaveRequest->end_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <td class="label">Durasi</td>
            <td class="sep">:</td>
            <td>
                {{ $leaveRequest->total_hari_kerja ?? $leaveRequest->total_days }} hari kerja
                @if($leaveRequest->total_hari_kerja && $leaveRequest->total_days)
                    ({{ $leaveRequest->total_days }} hari kalender)
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Alasan</td>
            <td class="sep">:</td>
            <td>{{ $leaveRequest->reason }}</td>
        </tr>
        @if($leaveRequest->alamat_cuti)
        <tr>
            <td class="label">Alamat Selama Cuti</td>
            <td class="sep">:</td>
            <td>{{ $leaveRequest->alamat_cuti }}</td>
        </tr>
        @endif
        @if($leaveRequest->telepon_cuti)
        <tr>
            <td class="label">Telepon Selama Cuti</td>
            <td class="sep">:</td>
            <td>{{ $leaveRequest->telepon_cuti }}</td>
        </tr>
        @endif
    </table>

    {{-- Alur Persetujuan --}}
    <div class="section-title">Persetujuan (Alur Khusus PPPK)</div>
    <table class="detail">
        <tr>
            <td class="label">Status</td>
            <td class="sep">:</td>
            <td><span class="status-box {{ $statusClass }}">{{ $statusText }}</span></td>
        </tr>
        @if($sekretaris)
        <tr>
            <td class="label">Pertimbangan Sekretaris</td>
            <td class="sep">:</td>
            <td>
                {{ ucfirst($leaveRequest->pertimbangan_atasan ?? '-') }}
                @if($leaveRequest->reviewed_at) &mdash; {{ $leaveRequest->reviewed_at->format('d M Y') }} @endif<br>
                <small>{{ $sekretaris->name }} ({{ $sekretaris->jabatan ?? 'Sekretaris' }})</small>
                @if($leaveRequest->catatan_atasan)
                    <br><small style="color:#555;font-style:italic;">Catatan: {{ $leaveRequest->catatan_atasan }}</small>
                @endif
            </td>
        </tr>
        @endif
        @if($ketua)
        <tr>
            <td class="label">Keputusan Ketua PN Natuna</td>
            <td class="sep">:</td>
            <td>
                {{ ucfirst($leaveRequest->keputusan_pejabat ?? '-') }}
                @if($leaveRequest->decided_at) &mdash; {{ $leaveRequest->decided_at->format('d M Y') }} @endif<br>
                <small>{{ $ketua->name }} ({{ $ketua->jabatan ?? 'Ketua' }})</small>
                @if($leaveRequest->catatan_pejabat)
                    <br><small style="color:#555;font-style:italic;">Catatan: {{ $leaveRequest->catatan_pejabat }}</small>
                @endif
            </td>
        </tr>
        @endif
    </table>

    {{-- Tanda Tangan --}}
    <div class="ttd-area">
        <div class="ttd-box">
            <div class="ttd-title">Pemohon</div>
            <div class="ttd-name">{{ $leaveRequest->user->name }}</div>
            <div class="ttd-nip">NIP. {{ $leaveRequest->user->nip ?? '-' }}</div>
        </div>
        <div class="ttd-box">
            <div class="ttd-title">Sekretaris</div>
            <div class="ttd-name">{{ $sekretaris?->name ?? '...........................' }}</div>
            <div class="ttd-nip">{{ $sekretaris ? 'NIP. ' . ($sekretaris->nip ?? '-') : '' }}</div>
        </div>
        <div class="ttd-box">
            <div class="ttd-title">Ketua Pengadilan Negeri Natuna</div>
            <div class="ttd-name">{{ $ketua?->name ?? '...........................' }}</div>
            <div class="ttd-nip">{{ $ketua ? 'NIP. ' . ($ketua->nip ?? '-') : '' }}</div>
        </div>
    </div>

    <div class="footer-note">
        Dicetak: {{ $generatedAt->format('d M Y, H:i') }} &nbsp;|&nbsp;
        No. Ref: {{ $leaveRequest->id }} &nbsp;|&nbsp;
        {{ $lembar['untuk'] }} &nbsp;|&nbsp;
        SiHEALING &mdash; Pengadilan Negeri Natuna
    </div>
</div>
@endforeach

</body>
</html>
