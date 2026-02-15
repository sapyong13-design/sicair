<?php

namespace App\Services;

use App\Models\LeaveRequest;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfExportService
{
    /**
     * Export single leave request to PDF
     */
    public function exportLeaveRequest(LeaveRequest $leaveRequest): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('pdfs.leave-request', [
            'leaveRequest' => $leaveRequest->load('user', 'atasanReviewer', 'pejabat'),
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Export multiple leave requests to PDF
     */
    public function exportLeaveRequests($leaveRequests): \Barryvdh\DomPDF\PDF
    {
        return Pdf::loadView('pdfs.leave-requests-list', [
            'leaveRequests' => $leaveRequests->load('user', 'atasanReviewer', 'pejabat'),
        ])->setPaper('a4', 'portrait');
    }

    /**
     * Export leave summary by year
     */
    public function exportLeaveSummary($user, $year): \Barryvdh\DomPDF\PDF
    {
        $cutiRecords = $user->cutiRecords()
            ->where('tahun', $year)
            ->get();

        return Pdf::loadView('pdfs.leave-summary', [
            'user' => $user,
            'year' => $year,
            'cutiRecords' => $cutiRecords,
        ])->setPaper('a4', 'portrait');
    }
}
