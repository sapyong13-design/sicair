@component('mail::message')
# Pengajuan Cuti Ditolak ❌

Pengajuan cuti {{ $leaveRequest->type_label }} Anda telah **ditolak** oleh {{ $rejectorName }}.

**Detail Pengajuan:**
- **Tipe Cuti:** {{ $leaveRequest->type_label }}
- **Tanggal Mulai:** {{ $leaveRequest->start_date->format('d M Y') }}
- **Tanggal Selesai:** {{ $leaveRequest->end_date->format('d M Y') }}
- **Hari Kerja:** {{ $leaveRequest->total_hari_kerja }} hari
- **Status:** <strong style="color: #ef4444;">Ditolak</strong>

@if($reason)
**Alasan Penolakan:**
{{ $reason }}
@endif

@component('mail::button', ['url' => route('leave.show', $leaveRequest)])
Lihat Detail
@endcomponent

Silakan hubungi atasan Anda untuk informasi lebih lanjut.

{{ config('app.name') }}
@endcomponent
