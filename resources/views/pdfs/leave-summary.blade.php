<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Leave Summary</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            color: #666;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 8px 12px;
            margin-bottom: 12px;
            border-left: 3px solid #007bff;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        .info-item {
            padding: 10px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        .info-label {
            font-size: 11px;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 14px;
            color: #333;
            margin-top: 5px;
            font-weight: bold;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .table th {
            background-color: #f0f0f0;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        .table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .summary-box {
            background-color: #e7f3ff;
            border: 1px solid #007bff;
            border-radius: 4px;
            padding: 15px;
            margin-top: 15px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 12px;
        }
        .summary-row:last-child {
            margin-bottom: 0;
            font-weight: bold;
            border-top: 1px solid #007bff;
            padding-top: 8px;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ringkasan Saldo Cuti Tahunan</h1>
            <p>Sistem Informasi Kesehatan - SI Healing</p>
        </div>

        <div class="section">
            <div class="section-title">Informasi Karyawan</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Nama</div>
                    <div class="info-value">{{ $user->name }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $user->email }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Posisi</div>
                    <div class="info-value">{{ $user->position ?? '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tahun</div>
                    <div class="info-value">{{ $year }}</div>
                </div>
            </div>
        </div>

        @if($cutiRecords->isNotEmpty())
            <div class="section">
                <div class="section-title">Detail Saldo Cuti</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Jenis Cuti</th>
                            <th width="15%">Alokasi</th>
                            <th width="15%">Digunakan</th>
                            <th width="15%">Sisa</th>
                            <th width="15%">Carry Over</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cutiRecords as $record)
                            <tr>
                                <td>{{ $record->jenis_cuti }}</td>
                                <td style="text-align: center;">{{ $record->alokasi_awal ?? 0 }}</td>
                                <td style="text-align: center;">{{ $record->digunakan ?? 0 }}</td>
                                <td style="text-align: center;">{{ $record->sisa ?? 0 }}</td>
                                <td style="text-align: center;">{{ $record->carry_over ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="summary-box">
                    <div class="summary-row">
                        <span>Total Alokasi:</span>
                        <span>{{ $cutiRecords->sum('alokasi_awal') ?? 0 }} hari</span>
                    </div>
                    <div class="summary-row">
                        <span>Total Digunakan:</span>
                        <span>{{ $cutiRecords->sum('digunakan') ?? 0 }} hari</span>
                    </div>
                    <div class="summary-row">
                        <span>Total Carry Over:</span>
                        <span>{{ $cutiRecords->sum('carry_over') ?? 0 }} hari</span>
                    </div>
                    <div class="summary-row">
                        <span>Sisa Cuti:</span>
                        <span>{{ ($cutiRecords->sum('alokasi_awal') ?? 0) - ($cutiRecords->sum('digunakan') ?? 0) }} hari</span>
                    </div>
                </div>
            </div>
        @else
            <div class="section">
                <p style="text-align: center; color: #999; padding: 20px;">
                    Tidak ada data saldo cuti untuk tahun {{ $year }}
                </p>
            </div>
        @endif

        <div class="footer">
            <p>Dokumen ini digenerate otomatis pada {{ now()->format('d M Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
