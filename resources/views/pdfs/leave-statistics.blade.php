<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 16px;
            color: #2c3e50;
        }
        .period {
            text-align: center;
            color: #666;
            margin-bottom: 15px;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #2c3e50;
            color: white;
            padding: 6px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .summary-box {
            background-color: #f0f9ff;
            border: 1px solid #3b82f6;
            padding: 10px;
            margin-top: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        .summary-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .summary-label {
            font-weight: bold;
        }
        .summary-value {
            color: #2c3e50;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Statistik Pengajuan Cuti yang Disetujui</h1>
    </div>

    <div class="period">
        Periode: {{ $startDate->format('d M Y') }} s/d {{ $endDate->format('d M Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Pegawai</th>
                <th>NIP</th>
                <th>Jenis Cuti</th>
                <th>Mulai</th>
                <th>Selesai</th>
                <th>Hari Kerja</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaveRequests as $leave)
            <tr>
                <td>{{ $leave->user->name }}</td>
                <td>{{ $leave->user->nip }}</td>
                <td>{{ $leave->type_label }}</td>
                <td>{{ $leave->start_date->format('d/m/Y') }}</td>
                <td>{{ $leave->end_date->format('d/m/Y') }}</td>
                <td style="text-align: center;">{{ $leave->total_hari_kerja ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 15px;">Tidak ada data pengajuan cuti yang disetujui dalam periode ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-row">
            <span class="summary-label">Total Pengajuan Disetujui:</span>
            <span class="summary-value">{{ $leaveRequests->count() }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">Total Hari Kerja:</span>
            <span class="summary-value">{{ $leaveRequests->sum('total_hari_kerja') }} hari</span>
        </div>
    </div>

    <div class="footer">
        <p>Dicetak: {{ $generatedAt->format('d M Y H:i:s') }}</p>
    </div>
</body>
</html>
