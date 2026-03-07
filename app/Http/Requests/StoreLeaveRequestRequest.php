<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'type'              => 'required|string|max:50',
            'start_date'        => 'required|date|after_or_equal:today',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'reason'            => 'required|string|min:10|max:500',
            'alamat_cuti'       => 'nullable|string|max:255',
            'telepon_cuti'      => 'nullable|string|max:20',
            'dokumen_pendukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required'              => 'Jenis cuti wajib dipilih.',
            'start_date.required'        => 'Tanggal mulai wajib diisi.',
            'start_date.after_or_equal'  => 'Tanggal mulai tidak boleh di masa lalu.',
            'end_date.required'          => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal'    => 'Tanggal selesai harus setelah tanggal mulai.',
            'reason.required'            => 'Alasan cuti wajib diisi.',
            'reason.min'                 => 'Alasan minimal 10 karakter.',
            'reason.max'                 => 'Alasan maksimal 500 karakter.',
            'dokumen_pendukung.max'      => 'File maksimal 5MB.',
            'dokumen_pendukung.mimes'    => 'File harus berformat PDF, JPG, atau PNG.',
        ];
    }
}
