{{-- Leave Balance Card with Visual Progress --}}
<div class="card sh-card sh-balance-card">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="card-title mb-1">
                    <i class="ti ti-calendar-stats me-2" style="color: var(--sh-primary);"></i>
                    Sisa Cuti {{ date('Y') }}
                </h3>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Breakdown penggunaan cuti tahunan Anda</p>
            </div>
        </div>

        <div class="row g-3 mb-4">
            {{-- Main Balance Circle --}}
            <div class="col-md-6 d-flex flex-column align-items-center">
                <div style="position: relative; width: 160px; height: 160px; margin-bottom: 1rem;">
                    <svg width="160" height="160" style="transform: rotate(-90deg);">
                        {{-- Background circle --}}
                        <circle cx="80" cy="80" r="70" fill="none" stroke="var(--sh-gray-200)" stroke-width="12"/>
                        {{-- Progress circle --}}
                        <circle cx="80" cy="80" r="70" fill="none" stroke="var(--sh-primary)" stroke-width="12"
                                stroke-dasharray="{{ ($sisaCuti / $totalHak) * 440 }} 440"
                                style="transition: stroke-dasharray 0.3s ease; stroke-linecap: round;"/>
                    </svg>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                        <div style="font-size: 2.2rem; font-weight: 700; color: var(--sh-primary);">{{ $sisaCuti }}</div>
                        <div style="font-size: 0.8rem; color: var(--sh-gray-600);">dari {{ $totalHak }} hari</div>
                    </div>
                </div>
                <div style="text-align: center; width: 100%;">
                    <div style="font-size: 0.9rem; color: var(--sh-gray-700); margin-bottom: 0.5rem;">
                        <strong>{{ round(($sisaCuti / $totalHak) * 100) }}%</strong> masih tersedia
                    </div>
                    <div style="font-size: 0.8rem; color: var(--sh-gray-600);">
                        @if($sisaCuti >= 5)
                            <span style="color: var(--sh-success);"><i class="ti ti-circle-check me-1"></i> Cukup untuk liburan</span>
                        @elseif($sisaCuti > 0)
                            <span style="color: var(--sh-warning);"><i class="ti ti-alert-circle me-1"></i> Segera gunakan</span>
                        @else
                            <span style="color: var(--sh-danger);"><i class="ti ti-circle-x me-1"></i> Sudah habis</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Breakdown by Type --}}
            <div class="col-md-6">
                <div style="space-y: 1rem;">
                    {{-- Cuti Tahunan --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <div style="font-weight: 600; color: var(--sh-gray-800); font-size: 0.95rem;">Cuti Tahunan</div>
                                <div style="font-size: 0.8rem; color: var(--sh-gray-600);">{{ $cutiTahunan }} hari</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--sh-primary); font-size: 1.1rem;">{{ $sisaCutiTahunan }}</div>
                                <div style="font-size: 0.75rem; color: var(--sh-gray-600);">sisa</div>
                            </div>
                        </div>
                        <div style="height: 6px; border-radius: 3px; background: var(--sh-gray-100);">
                            <div style="height: 100%; border-radius: 3px; background: var(--sh-primary); width: {{ $cutiTahunan > 0 ? (($sisaCutiTahunan / $cutiTahunan) * 100) : 0 }}%;"></div>
                        </div>
                    </div>

                    {{-- Cuti Sakit (jika ada) --}}
                    @if($cutiSakit > 0)
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <div style="font-weight: 600; color: var(--sh-gray-800); font-size: 0.95rem;">Cuti Sakit</div>
                                <div style="font-size: 0.8rem; color: var(--sh-gray-600);">Unlimited</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--sh-danger); font-size: 1.1rem;">{{ $penggunaanCutiSakit }}</div>
                                <div style="font-size: 0.75rem; color: var(--sh-gray-600);">digunakan</div>
                            </div>
                        </div>
                        <div style="height: 6px; border-radius: 3px; background: var(--sh-gray-100);">
                            <div style="height: 100%; border-radius: 3px; background: var(--sh-danger); width: {{ min((($penggunaanCutiSakit / 30) * 100), 100) }}%;"></div>
                        </div>
                    </div>
                    @endif

                    {{-- Carry Over (jika ada) --}}
                    @if($carryOver > 0)
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div>
                                <div style="font-weight: 600; color: var(--sh-gray-800); font-size: 0.95rem;">Carry Over</div>
                                <div style="font-size: 0.8rem; color: var(--sh-gray-600);">dari tahun lalu</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--sh-warning); font-size: 1.1rem;">{{ $carryOver }}</div>
                                <div style="font-size: 0.75rem; color: var(--sh-gray-600);">hari</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Progress bar saldo cuti --}}
        @php
            $pbHak  = isset($hakTotal) && $hakTotal > 0 ? $hakTotal : (isset($hak_dasar) && $hak_dasar > 0 ? $hak_dasar : 12);
            $pbUsed = isset($terpakai) ? $terpakai : (isset($digunakan) ? $digunakan : 0);
            $pbPct  = $pbHak > 0 ? min(100, round(($pbUsed / $pbHak) * 100)) : 0;
            $pbColor = $pbPct >= 80 ? '#dc2626' : ($pbPct >= 50 ? '#d97706' : '#166534');
        @endphp
        <div class="mt-3">
            <div style="background: #e2e8f0; border-radius: 99px; height: 8px; overflow: hidden;">
                <div style="width: {{ $pbPct }}%; background: {{ $pbColor }}; height: 100%; border-radius: 99px; transition: width 0.6s ease;"></div>
            </div>
            <div class="d-flex justify-content-between mt-1" style="font-size: 0.75rem; color: #64748b;">
                <span>Terpakai: {{ $pbUsed }} hari</span>
                <span>{{ $pbPct }}%</span>
            </div>
        </div>

        {{-- Prediction & Warning --}}
        @if($sisaCuti <= 5)
        <div style="background: var(--sh-warning-light); border-radius: 10px; padding: 0.75rem 1rem; margin-top: 1.5rem;">
            <div style="font-size: 0.85rem; color: var(--sh-warning);">
                <i class="ti ti-alert-triangle me-2"></i>
                <strong>Perhatian:</strong> Sisa cuti Anda tinggal {{ $sisaCuti }} hari. Segera rencanakan pengambilan cuti Anda untuk menghindari hangus.
            </div>
        </div>
        @else
        <div style="background: var(--sh-success-light); border-radius: 10px; padding: 0.75rem 1rem; margin-top: 1.5rem;">
            <div style="font-size: 0.85rem; color: var(--sh-success);">
                <i class="ti ti-check-circle me-2"></i>
                Anda masih memiliki waktu hingga akhir {{ date('F') }} untuk menggunakan cuti.
            </div>
        </div>
        @endif
    </div>
</div>

<style>
.sh-balance-card {
    border: none;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.sh-balance-card .card-body {
    padding: 1.5rem;
}

@media (max-width: 768px) {
    .sh-balance-card {
        margin-bottom: 1rem;
    }

    .sh-balance-card .row {
        flex-direction: column;
    }
}
</style>
