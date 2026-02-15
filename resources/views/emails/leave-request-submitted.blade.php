@component('mail::message')
# Pengajuan Cuti Baru

Pengajuan cuti {{ $leaveRequest->type_label }} dari **{{ $leaveRequest->user->name }}** telah diterima.

**Detail Pengajuan:**
- **Tipe Cuti:** {{ $leaveRequest->type_label }}
- **Tanggal Mulai:** {{ $leaveRequest->start_date->format('d M Y') }}
- **Tanggal Selesai:** {{ $leaveRequest->end_date->format('d M Y') }}
- **Hari Kerja:** {{ $leaveRequest->total_hari_kerja }} hari
- **Alasan:** {{ $leaveRequest->reason }}

@component('mail::button', ['url' => route('leave.show', $leaveRequest)])
Lihat Pengajuan
@endcomponent

Terima kasih,
{{ config('app.name') }}
@endcomponent
