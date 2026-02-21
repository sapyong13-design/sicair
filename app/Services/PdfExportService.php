<?php

namespace App\Services;

use App\Models\LeaveRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfExportService
{
    /**
     * Export single leave request as PDF (STB-optimized)
     */
    public static function exportLeaveRequest(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['user', 'atasanReviewer', 'pejabat']);

        $pdf = Pdf::loadView('pdfs.leave-request', [
            'leaveRequest' => $leaveRequest,
            'generatedAt' => now(),
        ]);

        // Optimize for STB: minimal margins
        $pdf->setPaper('A4');
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 10);
        $pdf->setOption('margin-right', 10);

        $filename = "Pengajuan-Cuti-{$leaveRequest->user->nip}-{$leaveRequest->start_date->format('Ymd')}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export multiple leave requests as PDF
     */
    public static function exportLeaveRequestSummary($leaveRequests)
    {
        $pdf = Pdf::loadView('pdfs.leave-summary', [
            'leaveRequests' => $leaveRequests,
            'totalCount' => $leaveRequests->count(),
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('margin-top', 8);
        $pdf->setOption('margin-bottom', 8);
        $pdf->setOption('margin-left', 8);
        $pdf->setOption('margin-right', 8);

        $filename = "Ringkasan-Cuti-" . now()->format('Ymd-His') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Export user leave balance report
     */
    public static function exportBalanceReport($user)
    {
        $pdf = Pdf::loadView('pdfs.balance-report', [
            'user' => $user,
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('A4');
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 10);
        $pdf->setOption('margin-right', 10);

        $filename = "Laporan-Saldo-Cuti-{$user->nip}-" . now()->format('Ymd') . ".pdf";

        return $pdf->download($filename);
    }

    /**
     * Export 3-copy leave form for PPPK employees (Lembar 1: Pegawai, 2: Sekretaris, 3: Ketua)
     */
    public static function exportPppkTripleForms(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['user', 'atasanReviewer', 'pejabat']);

        $pdf = Pdf::loadView('pdfs.leave-request-pppk-triple', [
            'leaveRequest' => $leaveRequest,
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('A4');
        $pdf->setOption('margin-top', 10);
        $pdf->setOption('margin-bottom', 10);
        $pdf->setOption('margin-left', 12);
        $pdf->setOption('margin-right', 12);

        $filename = "Izin-Cuti-PPPK-{$leaveRequest->user->nip}-{$leaveRequest->start_date->format('Ymd')}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export leave statistics for period
     */
    public static function exportLeaveStatistics($startDate, $endDate)
    {
        $leaveRequests = LeaveRequest::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', LeaveRequest::STATUS_DISETUJUI)
            ->with('user')
            ->get();

        $pdf = Pdf::loadView('pdfs.leave-statistics', [
            'leaveRequests' => $leaveRequests,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => now(),
        ]);

        $pdf->setPaper('A4', 'landscape');
        $pdf->setOption('margin-top', 8);
        $pdf->setOption('margin-bottom', 8);
        $pdf->setOption('margin-left', 8);
        $pdf->setOption('margin-right', 8);

        $filename = "Statistik-Cuti-{$startDate->format('Ymd')}-{$endDate->format('Ymd')}.pdf";

        return $pdf->download($filename);
    }
}
