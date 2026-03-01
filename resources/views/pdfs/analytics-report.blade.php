<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Analytics Cuti {{ $year }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #1e293b; background: #fff; padding: 24px; }
        h1 { font-size: 18px; color: #166534; margin-bottom: 4px; }
        h2 { font-size: 13px; color: #166534; margin: 20px 0 8px; border-bottom: 2px solid #166534; padding-bottom: 4px; }
        .subtitle { color: #64748b; font-size: 11px; margin-bottom: 16px; }
        .header { border-bottom: 3px solid #166534; padding-bottom: 12px; margin-bottom: 16px; }
        .stat-row { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
        .stat-box { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 14px; min-width: 120px; }
        .stat-num { font-size: 22px; font-weight: 700; color: #166534; }
        .stat-label { font-size: 10px; color: #64748b; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #f0fdf4; color: #166534; text-align: left; padding: 6px 8px; font-size: 11px; border-bottom: 2px solid #bbf7d0; }
        td { padding: 5px 8px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        tr:nth-child(even) td { background: #f8faf8; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 50px; font-size: 10px; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .footer { margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 8px; color: #94a3b8; font-size: 10px; text-align: center; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
<div class="header">
    <h1>Laporan Analytics Cuti Tahun {{ $year }}</h1>
    <div class="subtitle">Pengadilan Negeri Natuna &mdash; Dicetak: {{ date('d/m/Y H:i') }}</div>
</div>

@php $s = $analytics['summary'] ?? []; @endphp

<h2>Ringkasan</h2>
<div class="stat-row">
    <div class="stat-box">
        <div class="stat-num">{{ $s['total_requests'] ?? 0 }}</div>
        <div class="stat-label">Total Pengajuan</div>
    </div>
    <div class="stat-box">
        <div class="stat-num" style="color:#059669;">{{ $s['approved'] ?? 0 }}</div>
        <div class="stat-label">Disetujui</div>
    </div>
    <div class="stat-box">
        <div class="stat-num" style="color:#d97706;">{{ $s['pending'] ?? 0 }}</div>
        <div class="stat-label">Menunggu</div>
    </div>
    <div class="stat-box">
        <div class="stat-num" style="color:#dc2626;">{{ $s['rejected'] ?? 0 }}</div>
        <div class="stat-label">Ditolak</div>
    </div>
    <div class="stat-box">
        <div class="stat-num">{{ $s['total_days_approved'] ?? 0 }}</div>
        <div class="stat-label">Total Hari Disetujui</div>
    </div>
</div>

<h2>Perbandingan per Bagian</h2>
<table>
    <thead>
        <tr>
            <th>Bagian</th>
            <th>Total Pengajuan</th>
            <th>Total Hari</th>
            <th>Rata-rata Hari</th>
        </tr>
    </thead>
    <tbody>
        @foreach($byBagian as $bagian => $data)
        <tr>
            <td><strong>{{ $bagian }}</strong></td>
            <td>{{ $data['total_pengajuan'] }}</td>
            <td>{{ $data['total_hari'] }}</td>
            <td>{{ $data['rata_hari'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<h2>Trend 12 Bulan Terakhir</h2>
<table>
    <thead>
        <tr>
            @foreach($monthly12['labels'] as $label)
            <th style="font-size:9px;">{{ $label }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        <tr>
            @foreach($monthly12['values'] as $v)
            <td class="text-center">{{ $v }}</td>
            @endforeach
        </tr>
    </tbody>
</table>

<h2>Top Pengguna Cuti</h2>
<table>
    <thead>
        <tr><th>#</th><th>Nama</th><th>NIP</th><th>Jabatan</th><th>Jenis</th><th>Total Hari</th></tr>
    </thead>
    <tbody>
        @foreach($analytics['topUsers'] ?? [] as $i => $u)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $u['name'] }}</td>
            <td>{{ $u['nip'] }}</td>
            <td>{{ $u['jabatan'] ?? '-' }}</td>
            <td>{{ $u['most_common_type'] ?? '-' }}</td>
            <td><strong>{{ $u['total_days'] }}</strong></td>
        </tr>
        @endforeach
    </tbody>
</table>

<h2>Ringkasan Saldo Cuti</h2>
<table>
    <thead>
        <tr>
            <th>#</th><th>Nama</th><th>Jabatan</th><th>Unit</th>
            <th>Sisa Cuti</th><th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($leaveBalances ?? [] as $i => $u)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $u['name'] }}</td>
            <td>{{ $u['jabatan'] ?? '-' }}</td>
            <td>{{ $u['unit_kerja'] ?? '-' }}</td>
            <td>{{ $u['leave_balance'] ?? 0 }}</td>
            <td>
                @if(($u['leave_balance'] ?? 0) <= 0)
                <span class="badge badge-red">Habis</span>
                @elseif(($u['leave_balance'] ?? 0) <= 3)
                <span class="badge badge-yellow">Rendah</span>
                @else
                <span class="badge badge-green">Normal</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    Laporan ini digenerate otomatis oleh SiHEALING &mdash; Pengadilan Negeri Natuna
    &copy; {{ date('Y') }}
</div>

<script>window.onload = function() { window.print(); }</script>
</body>
</html>
