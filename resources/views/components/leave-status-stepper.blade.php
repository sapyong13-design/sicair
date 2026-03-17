@props(['leave'])
{{-- $leave: App\Models\LeaveRequest --}}
@isset($leave)
@php
use App\Models\LeaveRequest;

$status = $leave->status;

$step2 = match(true) {
    in_array($status, [
        LeaveRequest::STATUS_PERTIMBANGAN,
        LeaveRequest::STATUS_DISETUJUI,
        LeaveRequest::STATUS_APPROVED,
        LeaveRequest::STATUS_DITOLAK,
        LeaveRequest::STATUS_REJECTED,
        LeaveRequest::STATUS_DITANGGUHKAN,
    ]) => 'done',
    $status === LeaveRequest::STATUS_DIUBAH => 'warn',
    // diajukan, pending: menunggu pertimbangan atasan
    default => 'active',
};

$step3 = match(true) {
    in_array($status, [LeaveRequest::STATUS_DISETUJUI, LeaveRequest::STATUS_APPROVED]) => 'done',
    in_array($status, [LeaveRequest::STATUS_DITOLAK, LeaveRequest::STATUS_REJECTED])   => 'rejected',
    $status === LeaveRequest::STATUS_DITANGGUHKAN => 'warn',
    $status === LeaveRequest::STATUS_PERTIMBANGAN => 'active',
    default => 'pending',
};

// Warn icon: computed once — hanya satu dot yang pernah 'warn' untuk status manapun
$warnIcon = $status === LeaveRequest::STATUS_DITANGGUHKAN ? 'ti-clock-pause' : 'ti-edit';

$steps = [
    ['state' => 'done',  'label' => 'Diajukan'],
    ['state' => $step2,  'label' => 'Atasan'],
    ['state' => $step3,  'label' => 'Ketua'],
];
@endphp

{{--
  Catatan CSS naming: Plan ini menggunakan compound class pattern (.sc-step-dot.done)
  alih-alih single class (sc-step-done) seperti di spec — hasilnya identik secara visual.
  @once memastikan <style> hanya di-render sekali meskipun component di-loop berkali-kali.
--}}
@once
<style>
@keyframes sc-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
    50%       { box-shadow: 0 0 0 5px rgba(37, 99, 235, 0); }
}
.sc-step-dot.done     { background: var(--sc-success);    color: #fff; }
.sc-step-dot.active   { background: var(--sc-primary);    color: #fff; animation: sc-pulse 2s infinite; }
.sc-step-dot.rejected { background: var(--sc-danger);     color: #fff; }
.sc-step-dot.warn     { background: var(--sc-warning);    color: #fff; }
.sc-step-dot.pending  { background: var(--sc-gray-100);   color: var(--sc-text-muted); }
</style>
@endonce

<div class="d-flex align-items-start" style="min-width: 140px;">
    @foreach($steps as $i => $step)
        <div class="d-flex flex-column align-items-center">
            <div class="sc-step-dot {{ $step['state'] }} d-flex align-items-center justify-content-center"
                 style="width: 20px; height: 20px; border-radius: 50%; flex-shrink: 0;">
                @if($step['state'] === 'done')
                    <i class="ti ti-check" style="font-size: 0.6rem;"></i>
                @elseif($step['state'] === 'rejected')
                    <i class="ti ti-x" style="font-size: 0.6rem;"></i>
                @elseif($step['state'] === 'warn')
                    <i class="ti {{ $warnIcon }}" style="font-size: 0.6rem;"></i>
                @endif
            </div>
            <div style="font-size: 0.6rem; color: var(--sc-text-muted); margin-top: 3px; white-space: nowrap;">
                {{ $step['label'] }}
            </div>
        </div>
        @if(!$loop->last)
            @php $nextState = $steps[$i + 1]['state']; @endphp
            <div style="height: 2px; flex: 1; min-width: 12px;
                        background: {{ $nextState === 'done' ? 'var(--sc-success)' : 'var(--sc-gray-100)' }};
                        margin-top: 9px;"></div>
        @endif
    @endforeach
</div>
@endisset
