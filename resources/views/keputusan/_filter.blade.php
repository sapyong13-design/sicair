<div class="card sc-card mb-3">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('keputusan.index') }}" class="row g-2 align-items-end">
            @if(request('tab'))
            <input type="hidden" name="tab" value="{{ request('tab') }}">
            @endif

            @if($showStatus ?? false)
            <div class="col-sm-auto">
                <label class="form-label mb-1" style="font-size:0.8rem;">Status</label>
                <select name="status" class="form-select form-select-sm" style="min-width:140px;">
                    <option value="">Semua Status</option>
                    <option value="diajukan" {{ request('status') === 'diajukan' ? 'selected' : '' }}>Menunggu Review</option>
                    <option value="pertimbangan_atasan" {{ request('status') === 'pertimbangan_atasan' ? 'selected' : '' }}>Pertimbangan Ketua</option>
                    <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                    <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    <option value="diubah" {{ request('status') === 'diubah' ? 'selected' : '' }}>Diubah</option>
                    <option value="ditangguhkan" {{ request('status') === 'ditangguhkan' ? 'selected' : '' }}>Ditangguhkan</option>
                </select>
            </div>
            @endif

            @if($showType ?? false)
            <div class="col-sm-auto">
                <label class="form-label mb-1" style="font-size:0.8rem;">Jenis Cuti</label>
                <select name="type" class="form-select form-select-sm" style="min-width:160px;">
                    <option value="">Semua Jenis</option>
                    @foreach(\App\Models\LeaveRequest::typeLabels() as $value => $label)
                    <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            @if($showDate ?? false)
            <div class="col-sm-auto">
                <label class="form-label mb-1" style="font-size:0.8rem;">Dari</label>
                <input type="date" name="start_date" class="form-control form-control-sm"
                    value="{{ request('start_date') }}" style="min-width:130px;">
            </div>
            <div class="col-sm-auto">
                <label class="form-label mb-1" style="font-size:0.8rem;">Sampai</label>
                <input type="date" name="end_date" class="form-control form-control-sm"
                    value="{{ request('end_date') }}" style="min-width:130px;">
            </div>
            @endif

            @if($showNameSearch ?? false)
            <div class="col-sm-auto">
                <label class="form-label mb-1" style="font-size:0.8rem;">Nama Pegawai</label>
                <input type="text"
                       name="q"
                       class="form-control form-control-sm"
                       placeholder="Cari nama..."
                       value="{{ request('q') }}"
                       style="min-width:160px;">
            </div>
            @endif
            <div class="col-sm-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm sc-btn-primary">
                    <i class="ti ti-search me-1"></i> Filter
                </button>
                <a href="{{ route('keputusan.index', request('tab') ? ['tab' => request('tab')] : []) }}"
                   class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>
