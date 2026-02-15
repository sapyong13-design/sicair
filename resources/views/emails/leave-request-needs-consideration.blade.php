@component('mail::message')
# Pengajuan Cuti Memerlukan Pertimbangan

Pengajuan cuti {{ $leaveRequest->type_label }} dari **{{ $leaveRequest->user->name }}** memerlukan pertimbangan lebih lanjut dari Ketua.

**Detail Pengajuan:**
- **Tipe Cuti:** {{ $leaveRequest->type_label }}
- **Tanggal Mulai:** {{ $leaveRequest->start_date->format('d M Y') }}
- **Tanggal Selesai:** {{ $leaveRequest->end_date->format('d M Y') }}
- **Hari Kerja:** {{ $leaveRequest->total_hari_kerja }} hari
- **Status:** <strong style="color: #f59e0b;">Menunggu Pertimbangan</strong>

@component('mail::button', ['url' => route('leave.show', $leaveRequest)])
Lihat Pengajuan
@endcomponent

Silakan tinjau dan berikan pertimbangan Anda.

{{ config('app.name') }}
@endcomponent
