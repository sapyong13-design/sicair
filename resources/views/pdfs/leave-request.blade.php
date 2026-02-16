<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #2c3e50;
        }
        .header p {
            margin: 3px 0;
            font-size: 11px;
            color: #666;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            font-weight: bold;
            font-size: 13px;
            color: #2c3e50;
            margin-bottom: 8px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            color: white;
        }
        .status-diajukan { background-color: #f59e0b; }
        .status-disetujui { background-color: #10b981; }
        .status-ditolak { background-color: #ef4444; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        td {
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }
        td.label {
            font-weight: bold;
            width: 35%;
            color: #555;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Pengajuan Cuti</h1>
        <p>Sistem Informasi Cuti Tahunan - Pengadilan Negeri Natuna</p>
    </div>

    <div class="section">
        <div class="section-title">Informasi Pemohon</div>
        <table>
            <tr>
                <td class="label">Nama:</td>
                <td>{{ $leaveRequest->user->name }}</td>
            </tr>
            <tr>
                <td class="label">NIP:</td>
                <td>{{ $leaveRequest->user->nip }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan:</td>
                <td>{{ $leaveRequest->user->jabatan }}</td>
            </tr>
            <tr>
                <td class="label">Unit Kerja:</td>
                <td>{{ $leaveRequest->user->unit_kerja }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Detail Pengajuan</div>
        <table>
            <tr>
                <td class="label">Jenis Cuti:</td>
                <td>{{ $leaveRequest->type_label }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Mulai:</td>
                <td>{{ $leaveRequest->start_date->format('d M Y') }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Selesai:</td>
                <td>{{ $leaveRequest->end_date->format('d M Y') }}</td>
            </tr>
            <tr>
                <td class="label">Durasi:</td>
                <td>{{ $leaveRequest->total_hari_kerja ?? $leaveRequest->total_days }} hari kerja</td>
            </tr>
            <tr>
                <td class="label">Alasan:</td>
                <td>{{ $leaveRequest->reason }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Status</div>
        <table>
            <tr>
                <td class="label">Status:</td>
                <td>
                    <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $leaveRequest->status)) }}">
                        {{ $leaveRequest->status_label }}
                    </span>
                </td>
            </tr>
            @if($leaveRequest->catatan_pejabat)
            <tr>
                <td class="label">Catatan:</td>
                <td>{{ $leaveRequest->catatan_pejabat }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="footer">
        <p>Dicetak: {{ $generatedAt->format('d M Y H:i:s') }}</p>
        <p>ID: {{ $leaveRequest->id }}</p>
    </div>
</body>
</html>
