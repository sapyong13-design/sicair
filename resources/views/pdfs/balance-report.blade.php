<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
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
        .info-box {
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 10px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            width: 30%;
            font-weight: bold;
            color: #555;
        }
        .info-value {
            width: 70%;
            color: #333;
        }
        .balance-status {
            background-color: #f0fdf4;
            border: 2px solid #10b981;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
            border-radius: 5px;
        }
        .balance-status h2 {
            margin: 0 0 10px 0;
            color: #10b981;
            font-size: 14px;
        }
        .balance-status .number {
            font-size: 32px;
            font-weight: bold;
            color: #10b981;
        }
        .balance-status .unit {
            color: #666;
            font-size: 12px;
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
        <h1>Laporan Saldo Cuti Tahunan</h1>
    </div>

    <div class="info-box">
        <div class="info-row">
            <div class="info-label">Nama Pegawai:</div>
            <div class="info-value">{{ $user->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">NIP:</div>
            <div class="info-value">{{ $user->nip }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Jabatan:</div>
            <div class="info-value">{{ $user->jabatan }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Unit Kerja:</div>
            <div class="info-value">{{ $user->unit_kerja }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Golongan/Ruang:</div>
            <div class="info-value">{{ $user->golongan_ruang }}</div>
        </div>
    </div>

    <div class="balance-status">
        <h2>Saldo Cuti Tahunan</h2>
        <div class="number">{{ $user->leave_balance }}</div>
        <div class="unit">hari kerja</div>
    </div>

    <div style="margin-top: 20px; padding: 10px; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 3px;">
        <strong>Catatan:</strong> Laporan ini menunjukkan saldo cuti per {{ $generatedAt->format('d M Y') }}.
    </div>

    <div class="footer">
        <p>Dicetak: {{ $generatedAt->format('d M Y H:i:s') }}</p>
    </div>
</body>
</html>
