<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    /**
     * View document in browser
     */
    public function view(LeaveRequest $leaveRequest, $documentType = 'pendukung')
    {
        $this->authorizeDocument($leaveRequest);

        $path = $this->getDocumentPath($leaveRequest, $documentType);

        if (!$path || !Storage::disk('public')->exists($path)) {
            return back()->with('error', 'Dokumen tidak ditemukan.');
        }

        return response()->file(Storage::disk('public')->path($path));
    }

    /**
     * Download document
     */
    public function download(LeaveRequest $leaveRequest, $documentType = 'pendukung')
    {
        $this->authorizeDocument($leaveRequest);

        $path = $this->getDocumentPath($leaveRequest, $documentType);

        if (!$path || !Storage::disk('public')->exists($path)) {
            return back()->with('error', 'Dokumen tidak ditemukan.');
        }

        $filename = $this->getDownloadFilename($leaveRequest, $documentType);

        return Storage::disk('public')->download($path, $filename);
    }

    /**
     * Get document list for a leave request
     */
    public function list(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();

        // Check authorization
        if ($leaveRequest->user_id !== $user->id && !$user->isAdmin() && !$user->isKetua() && !$user->isAtasan()) {
            return back()->with('error', 'Anda tidak memiliki akses ke dokumen ini.');
        }

        $documents = $this->getAvailableDocuments($leaveRequest);

        return view('documents.list', compact('leaveRequest', 'documents'));
    }

    /**
     * Get all documents for leave request (API endpoint)
     */
    public function getDocuments(LeaveRequest $leaveRequest)
    {
        $this->authorizeDocument($leaveRequest);

        $documents = $this->getAvailableDocuments($leaveRequest);

        return response()->json([
            'success' => true,
            'documents' => $documents,
        ]);
    }

    /**
     * Upload supporting document for leave request
     */
    public function upload(Request $request, LeaveRequest $leaveRequest)
    {
        // Only the leave requester can upload documents
        if ($leaveRequest->user_id !== Auth::id()) {
            return back()->with('error', 'Anda tidak memiliki akses untuk upload dokumen ini.');
        }

        $request->validate([
            'dokumen' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'dokumen.required' => 'File dokumen harus dipilih.',
            'dokumen.file' => 'Input harus berupa file.',
            'dokumen.mimes' => 'File harus berformat PDF, JPG, JPEG, atau PNG.',
            'dokumen.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        // Delete old document if exists
        if ($leaveRequest->dokumen_pendukung && Storage::disk('public')->exists($leaveRequest->dokumen_pendukung)) {
            Storage::disk('public')->delete($leaveRequest->dokumen_pendukung);
        }

        // Store new document
        $path = $request->file('dokumen')->store('leave-documents', 'public');

        // Update leave request
        $leaveRequest->update([
            'dokumen_pendukung' => $path,
        ]);

        return back()->with('success', 'Dokumen berhasil diupload.');
    }

    /**
     * Check authorization for document access
     */
    private function authorizeDocument(LeaveRequest $leaveRequest): void
    {
        $user = Auth::user();

        // Owner, admin, ketua, or atasan can access
        if ($leaveRequest->user_id !== $user->id && !$user->isAdmin() && !$user->isKetua() && !$user->isAtasan()) {
            abort(403, 'Unauthorized document access');
        }
    }

    /**
     * Get document path based on type
     */
    private function getDocumentPath(LeaveRequest $leaveRequest, string $type): ?string
    {
        return match ($type) {
            'pendukung' => $leaveRequest->dokumen_pendukung,
            default => null,
        };
    }

    /**
     * Get download filename
     */
    private function getDownloadFilename(LeaveRequest $leaveRequest, string $type): string
    {
        $userName = str_slug($leaveRequest->user->name);
        $leaveType = str_slug($leaveRequest->type);
        $date = $leaveRequest->created_at->format('Y-m-d');

        return match ($type) {
            'pendukung' => "dokumen-{$leaveType}-{$userName}-{$date}." . $this->getFileExtension($leaveRequest->dokumen_pendukung),
            default => "dokumen-{$date}.pdf",
        };
    }

    /**
     * Get file extension from path
     */
    private function getFileExtension(string $path): string
    {
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * Get available documents for leave request
     */
    private function getAvailableDocuments(LeaveRequest $leaveRequest): array
    {
        $documents = [];

        if ($leaveRequest->dokumen_pendukung && Storage::disk('public')->exists($leaveRequest->dokumen_pendukung)) {
            $documents[] = [
                'type' => 'pendukung',
                'label' => 'Dokumen Pendukung',
                'path' => $leaveRequest->dokumen_pendukung,
                'url' => Storage::disk('public')->url($leaveRequest->dokumen_pendukung),
                'size' => $this->formatFileSize(Storage::disk('public')->size($leaveRequest->dokumen_pendukung)),
                'extension' => $this->getFileExtension($leaveRequest->dokumen_pendukung),
                'mime' => Storage::disk('public')->mimeType($leaveRequest->dokumen_pendukung),
            ];
        }

        return $documents;
    }

    /**
     * Format file size for display
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Get Bootstrap icon for document type
     */
    public function getDocumentIcon(string $extension): string
    {
        return match (strtolower($extension)) {
            'pdf' => 'file-pdf',
            'doc', 'docx' => 'file-word',
            'xls', 'xlsx' => 'file-excel',
            'jpg', 'jpeg', 'png', 'gif' => 'file-image',
            'zip', 'rar', '7z' => 'file-zip',
            default => 'file-earmark',
        };
    }
}
