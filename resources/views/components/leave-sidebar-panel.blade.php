{{-- resources/views/components/leave-sidebar-panel.blade.php --}}
{{-- Slide-in panel kalender. Dikontrol via JS: showSidePanel() dan closeSidePanel() --}}
<div id="calSidePanel"
     style="position:fixed; top:0; right:0; height:100vh; width:320px; background:#fff;
            box-shadow:-4px 0 24px rgba(0,0,0,0.12); transform:translateX(100%);
            transition:transform 0.25s ease; z-index:1055; overflow-y:auto;"
     aria-label="Detail tanggal">

    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between p-3 border-bottom"
         style="background:var(--sc-primary); color:#fff;">
        <div>
            <div id="panelDateLabel" class="fw-bold" style="font-size:1rem;"></div>
            <div id="panelHolidayLabel" class="mt-1" style="font-size:0.8rem; opacity:0.85;"></div>
        </div>
        <button type="button" onclick="closeSidePanel()"
                class="btn btn-sm"
                style="color:#fff; background:rgba(255,255,255,0.2); border-radius:8px;"
                aria-label="Tutup panel">
            <i class="ti ti-x"></i>
        </button>
    </div>

    {{-- Pegawai yang cuti --}}
    <div class="p-3">
        <div class="fw-semibold mb-2" style="font-size:0.85rem; color:#64748b; text-transform:uppercase; letter-spacing:0.05em;">
            Yang Cuti Hari Ini
        </div>
        <div id="panelLeaveList">
            <div class="text-muted" style="font-size:0.85rem;">Tidak ada yang cuti.</div>
        </div>
    </div>

    {{-- Tombol ajukan cuti --}}
    <div class="p-3 border-top">
        <a id="panelAjukanBtn" href="#"
           class="btn btn-primary w-100"
           style="border-radius:10px;">
            <i class="ti ti-calendar-plus me-2"></i>Ajukan Cuti Tanggal Ini
        </a>
    </div>
</div>

{{-- Overlay --}}
<div id="calSidePanelOverlay"
     onclick="closeSidePanel()"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.3); z-index:1054;"></div>
