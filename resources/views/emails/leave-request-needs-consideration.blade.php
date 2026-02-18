<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f59e0b; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
        .content { background-color: #fffbeb; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
        .detail { margin: 15px 0; }
        .detail-row { margin-bottom: 8px; }
        .label { font-weight: bold; color: #555; }
        .button { display: inline-block; background-color: #f59e0b; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .footer { margin-top: 20px; text-align: center; color: #999; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏳ Pengajuan Cuti Memerlukan Pertimbangan</h1>
        </div>
        <div class="content">
            <p>Pengajuan cuti <strong>{{ $leaveRequest->type_label }}</strong> dari <strong>{{ $leaveRequest->user->name }}</strong> memerlukan pertimbangan lebih lanjut dari Ketua.</p>

            <div class="detail">
                <h3 style="margin-top: 20px;">Detail Pengajuan</h3>
                <div class="detail-row"><span class="label">Nama Pegawai:</span> {{ $leaveRequest->user->name }}</div>
                <div class="detail-row"><span class="label">NIP:</span> {{ $leaveRequest->user->nip }}</div>
                <div class="detail-row"><span class="label">Jenis Cuti:</span> {{ $leaveRequest->type_label }}</div>
                <div class="detail-row"><span class="label">Tanggal Mulai:</span> {{ $leaveRequest->start_date->format('d M Y') }}</div>
                <div class="detail-row"><span class="label">Tanggal Selesai:</span> {{ $leaveRequest->end_date->format('d M Y') }}</div>
                <div class="detail-row"><span class="label">Hari Kerja:</span> {{ $leaveRequest->total_hari_kerja }} hari</div>
                <div class="detail-row"><span class="label">Status:</span> <strong style="color: #f59e0b;">Menunggu Pertimbangan</strong></div>
            </div>

            <center>
                <a href="{{ route('leave.show', $leaveRequest) }}" class="button">Lihat Pengajuan</a>
            </center>

            <p style="margin-top: 20px; text-align: center; color: #666;">
                <strong>Silakan tinjau dan berikan keputusan Anda.</strong>
            </p>

            <div class="footer">
                <p>{{ config('app.name') }}</p>
            </div>
        </div>
    </div>
</body>
</html>
