{{-- Export & Print Actions Component --}}
<div class="export-actions-dropdown">
    <div class="dropdown">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="ti ti-download me-2"></i> Export & Print
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li>
                <h6 class="dropdown-header">Format Dokumen</h6>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('export.leave-pdf', $leaveRequest->id) }}" target="_blank">
                    <i class="ti ti-file-pdf me-2" style="color: #dc2626;"></i>
                    Export sebagai PDF
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('export.leave-excel', $leaveRequest->id) }}">
                    <i class="ti ti-file-excel me-2" style="color: #16a34a;"></i>
                    Export sebagai Excel
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="javascript:window.print()">
                    <i class="ti ti-printer me-2"></i>
                    Print
                </a>
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li>
                <h6 class="dropdown-header">Laporan</h6>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('reports.summary-pdf') }}" target="_blank">
                    <i class="ti ti-report-analytics me-2" style="color: #2563eb;"></i>
                    Laporan Summary
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('reports.monthly-report') }}">
                    <i class="ti ti-calendar-month me-2" style="color: #9333ea;"></i>
                    Laporan Bulanan
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="{{ route('reports.annual-report') }}">
                    <i class="ti ti-chart-line me-2" style="color: #f59e0b;"></i>
                    Laporan Tahunan
                </a>
            </li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li>
                <h6 class="dropdown-header">Bagikan</h6>
            </li>
            <li>
                <a class="dropdown-item" href="javascript:shareViaEmail()">
                    <i class="ti ti-mail me-2" style="color: #0284c7;"></i>
                    Bagikan via Email
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="javascript:copyShareLink()">
                    <i class="ti ti-link me-2" style="color: #7c3aed;"></i>
                    Salin Tautan Bagian
                </a>
            </li>
        </ul>
    </div>
</div>

{{-- Print Styles --}}
<style media="print">
    @media print {
        body {
            background: white;
        }

        .no-print {
            display: none !important;
        }

        .print-container {
            padding: 20mm;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .print-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .print-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .print-header p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }

        .print-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .print-section h2 {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }

        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        .print-table th,
        .print-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 12px;
        }

        .print-table th {
            background-color: #f3f4f6;
            font-weight: 600;
        }

        .print-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 12px;
        }

        .print-row label {
            font-weight: 600;
            width: 35%;
        }

        .print-row value {
            width: 65%;
        }

        .signature-block {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-item {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            font-size: 12px;
            font-weight: 600;
        }
    }
</style>

<script>
function shareViaEmail() {
    const subject = encodeURIComponent('Pengajuan Cuti - {{ $leaveRequest->type_label }}');
    const body = encodeURIComponent(`
Saya ingin membagikan pengajuan cuti saya kepada Anda.

Jenis Cuti: {{ $leaveRequest->type_label }}
Periode: {{ $leaveRequest->start_date->format('d M Y') }} - {{ $leaveRequest->end_date->format('d M Y') }}
Durasi: {{ $leaveRequest->total_days }} hari
Status: {{ $leaveRequest->status }}

Lihat detail: ${window.location.href}
    `);
    window.location.href = `mailto:?subject=${subject}&body=${body}`;
}

function copyShareLink() {
    const link = window.location.href;
    navigator.clipboard.writeText(link).then(() => {
        // Show success toast
        const toast = document.createElement('div');
        toast.className = 'alert alert-success position-fixed bottom-0 end-0 m-3';
        toast.innerHTML = '<i class="ti ti-check me-2"></i> Link berhasil disalin!';
        toast.style.cssText = 'z-index: 9999; animation: slideIn 0.3s ease;';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    });
}

function printDocument() {
    window.print();
}

// Print styles injection
document.addEventListener('beforeprint', function() {
    document.body.classList.add('printing');
});

document.addEventListener('afterprint', function() {
    document.body.classList.remove('printing');
});
</script>
