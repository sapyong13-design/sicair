<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Leave Request</title>
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
        .row {
            display: flex;
            gap: 20px;
            margin-bottom: 10px;
        }
        .col {
            flex: 1;
        }
        .label {
            font-size: 11px;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
        }
        .value {
            font-size: 12px;
            color: #333;
            margin-top: 3px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
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
        .status-disetujui {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .status-ditolak {
            background-color: #f8d7da;
            color: #842029;
        }
        .status-approved {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #842029;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
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
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }
        .signature {
            width: 35%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 40px;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Surat Pengajuan Cuti</h1>
            <p>Sistem Informasi Kesehatan - SI Healing</p>
        </div>

        <div class="section">
            <div class="section-title">Informasi Karyawan</div>
            <div class="row">
                <div class="col">
                    <div class="label">Nama</div>
                    <div class="value">{{ $leaveRequest->user->name }}</div>
                </div>
                <div class="col">
                    <div class="label">Email</div>
                    <div class="value">{{ $leaveRequest->user->email }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="label">Posisi</div>
                    <div class="value">{{ $leaveRequest->user->position ?? '-' }}</div>
                </div>
                <div class="col">
                    <div class="label">Departemen</div>
                    <div class="value">{{ $leaveRequest->user->department ?? '-' }}</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Detail Pengajuan Cuti</div>
            <div class="row">
                <div class="col">
                    <div class="label">Jenis Cuti</div>
                    <div class="value">{{ $leaveRequest->type }}</div>
                </div>
                <div class="col">
                    <div class="label">Status</div>
                    <div class="value">
                        <span class="status-badge status-{{ strtolower($leaveRequest->status) }}">
                            {{ ucfirst(str_replace('_', ' ', $leaveRequest->status)) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="label">Tanggal Mulai</div>
                    <div class="value">{{ $leaveRequest->start_date->format('d M Y') }}</div>
                </div>
                <div class="col">
                    <div class="label">Tanggal Selesai</div>
                    <div class="value">{{ $leaveRequest->end_date->format('d M Y') }}</div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="label">Jumlah Hari</div>
                    <div class="value">{{ $leaveRequest->number_of_days }} hari</div>
                </div>
                <div class="col">
                    <div class="label">Tanggal Pengajuan</div>
                    <div class="value">{{ $leaveRequest->created_at->format('d M Y H:i') }}</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Alasan Cuti</div>
            <div class="value" style="padding: 10px; background-color: #f9f9f9; border-radius: 4px;">
                {{ $leaveRequest->reason ?? 'Tidak ada keterangan' }}
            </div>
        </div>

        @if($leaveRequest->atasanReviewer || $leaveRequest->pejabat)
            <div class="section">
                <div class="section-title">Approval History</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Peran</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($leaveRequest->atasanReviewer)
                            <tr>
                                <td>Atasan Langsung</td>
                                <td>{{ $leaveRequest->atasanReviewer->name }}</td>
                                <td>
                                    @if($leaveRequest->atasan_review_status)
                                        <span class="status-badge status-{{ strtolower($leaveRequest->atasan_review_status) }}">
                                            {{ $leaveRequest->atasan_review_status }}
                                        </span>
                                    @else
                                        <span>Pending</span>
                                    @endif
                                </td>
                                <td>{{ $leaveRequest->atasan_review_date?->format('d M Y') ?? '-' }}</td>
                            </tr>
                        @endif
                        @if($leaveRequest->pejabat)
                            <tr>
                                <td>Pejabat Pemerintah</td>
                                <td>{{ $leaveRequest->pejabat->name }}</td>
                                <td>
                                    @if($leaveRequest->pejabat_review_status)
                                        <span class="status-badge status-{{ strtolower($leaveRequest->pejabat_review_status) }}">
                                            {{ $leaveRequest->pejabat_review_status }}
                                        </span>
                                    @else
                                        <span>Pending</span>
                                    @endif
                                </td>
                                <td>{{ $leaveRequest->pejabat_review_date?->format('d M Y') ?? '-' }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @endif

        <div class="footer">
            <div class="signature">
                <div class="label">Pemohon</div>
                <div class="signature-line">{{ $leaveRequest->user->name }}</div>
            </div>
            <div class="signature">
                <div class="label">Disetujui</div>
                <div class="signature-line">
                    @if($leaveRequest->pejabat)
                        {{ $leaveRequest->pejabat->name }}
                    @else
                        ________________
                    @endif
                </div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 30px; font-size: 10px; color: #999;">
            <p>Dokumen ini digenerated otomatis pada {{ now()->format('d M Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
