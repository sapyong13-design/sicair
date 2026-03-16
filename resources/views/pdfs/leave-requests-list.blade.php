<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Leave Requests Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 22px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 11px;
            color: #666;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .table th {
            background-color: #f0f0f0;
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        .table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-diajukan {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-pertimbangan {
            background-color: #cfe2ff;
            color: #084298;
        }
        .status-disetujui,
        .status-approved {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .status-ditolak,
        .status-rejected {
            background-color: #f8d7da;
            color: #842029;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Laporan Pengajuan Cuti</h1>
            <p>Sistem Cuti Administrasi Elektronik - SiCAIR</p>
            <p style="margin-top: 5px;">Periode: {{ now()->format('d M Y') }}</p>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="15%">Nama Karyawan</th>
                    <th width="12%">Jenis Cuti</th>
                    <th width="12%">Tgl Mulai</th>
                    <th width="12%">Tgl Selesai</th>
                    <th width="8%">Hari</th>
                    <th width="15%">Status</th>
                    <th width="11%">Tgl Pengajuan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveRequests as $key => $leave)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $leave->user->name }}</td>
                        <td>{{ $leave->type }}</td>
                        <td>{{ $leave->start_date->format('d M Y') }}</td>
                        <td>{{ $leave->end_date->format('d M Y') }}</td>
                        <td style="text-align: center;">{{ $leave->number_of_days }}</td>
                        <td>
                            <span class="status-badge status-{{ strtolower($leave->status) }}">
                                {{ ucfirst(str_replace('_', ' ', $leave->status)) }}
                            </span>
                        </td>
                        <td>{{ $leave->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 15px;">
                            Tidak ada data pengajuan cuti
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <p>Total Pengajuan: {{ $leaveRequests->count() }}</p>
            <p style="margin-top: 10px;">Dokumen ini digenerate otomatis pada {{ now()->format('d M Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
