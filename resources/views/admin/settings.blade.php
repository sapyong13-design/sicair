@extends('layouts.app')

@section('title', 'Pengaturan Sistem — SiCAIR')

@section('content')
<nav class="sc-breadcrumb" aria-label="Breadcrumb">
    <a href="{{ route('dashboard') }}">Dashboard</a>
    <span class="sc-breadcrumb-sep" aria-hidden="true"><i class="ti ti-chevron-right" style="font-size: 0.7rem;"></i></span>
    <span class="sc-breadcrumb-current">Pengaturan Sistem</span>
</nav>

<div class="sc-page-header">
    <h2 class="sc-page-title mb-0">
        <i class="ti ti-settings me-2" style="color: var(--sc-primary);"></i> Pengaturan Sistem
    </h2>
    <div class="text-muted" style="font-size: 0.85rem;">Konfigurasi nilai-nilai sistem SiCAIR</div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show mb-4" style="border-radius: 12px;">
    <i class="ti ti-check me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf
    @method('PUT')

    @php
    $groupLabels = [
        'umum'        => ['label' => 'Umum', 'icon' => 'ti-building'],
        'cuti'        => ['label' => 'Cuti', 'icon' => 'ti-calendar-off'],
        'notifikasi'  => ['label' => 'Notifikasi', 'icon' => 'ti-bell'],
        'general'     => ['label' => 'General', 'icon' => 'ti-settings'],
    ];
    @endphp

    @foreach($settings as $group => $items)
    @php $gl = $groupLabels[$group] ?? ['label' => ucfirst($group), 'icon' => 'ti-settings']; @endphp
    <div class="card sc-card mb-4">
        <div class="card-header border-0 py-3" style="background: transparent;">
            <h6 class="mb-0 fw-bold">
                <i class="ti {{ $gl['icon'] }} me-2" style="color: var(--sc-primary);"></i>
                {{ $gl['label'] }}
            </h6>
        </div>
        <div class="card-body pt-0">
            @foreach($items as $setting)
            <div class="mb-4">
                <label class="form-label fw-semibold" style="font-size: 0.85rem;">
                    {{ $setting->label }}
                </label>
                @if($setting->type === 'boolean')
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox"
                               name="settings[{{ $setting->key }}]"
                               value="1"
                               {{ $setting->value ? 'checked' : '' }}
                               style="width: 2.5rem; height: 1.3rem;">
                    </div>
                @elseif($setting->type === 'textarea')
                    <textarea name="settings[{{ $setting->key }}]"
                              class="form-control"
                              rows="3"
                              style="border-radius: 10px; border: 2px solid #e2e8f0; font-size: 0.85rem;">{{ $setting->value }}</textarea>
                @elseif($setting->type === 'number')
                    <input type="number" name="settings[{{ $setting->key }}]"
                           class="form-control"
                           value="{{ $setting->value }}"
                           min="0"
                           style="border-radius: 10px; border: 2px solid #e2e8f0; max-width: 180px; height: 42px;">
                @else
                    <input type="text" name="settings[{{ $setting->key }}]"
                           class="form-control"
                           value="{{ $setting->value }}"
                           style="border-radius: 10px; border: 2px solid #e2e8f0; height: 42px;">
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="d-flex gap-2">
        <button type="submit" class="btn sc-btn-primary" style="border-radius: 10px;">
            <i class="ti ti-device-floppy me-1"></i> Simpan Pengaturan
        </button>
        <a href="{{ route('admin.backup') }}" class="btn btn-outline-secondary" style="border-radius: 10px;">
            <i class="ti ti-database-export me-1"></i> Manajemen Backup
        </a>
    </div>
</form>
@endsection
