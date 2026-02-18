<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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
        .summary {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9fafb;
            border-left: 3px solid #3b82f6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #2c3e50;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .status-approved {
            color: #10b981;
            font-weight: bold;
        }
        .status-pending {
            color: #f59e0b;
            font-weight: bold;
        }
        .status-rejected {
            color: #ef4444;
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
        <h1>Ringkasan Pengajuan Cuti</h1>
        <p>Periode: {{ $generatedAt->format('d M Y') }}</p>
    </div>

    <div class="summary">
        <strong>Total Pengajuan: {{ $totalCount }}</strong>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Pegawai</th>
                <th>NIP</th>
                <th>Jenis Cuti</th>
                <th>Tanggal Mulai</th>
                <th>Tanggal Selesai</th>
                <th>Hari Kerja</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($leaveRequests as $leave)
            <tr>
                <td>{{ $leave->user->name }}</td>
                <td>{{ $leave->user->nip }}</td>
                <td>{{ $leave->type_label }}</td>
                <td>{{ $leave->start_date->format('d/m/Y') }}</td>
                <td>{{ $leave->end_date->format('d/m/Y') }}</td>
                <td>{{ $leave->total_hari_kerja ?? '-' }}</td>
                <td class="status-{{ strtolower(str_replace(' ', '-', $leave->status)) }}">{{ $leave->status_label }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak: {{ $generatedAt->format('d M Y H:i:s') }}</p>
    </div>
</body>
</html>
