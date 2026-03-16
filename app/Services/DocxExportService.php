<?php

namespace App\Services;

use App\Models\CutiRecord;
use App\Models\LeaveRequest;
use App\Models\User;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;

class DocxExportService
{
    /**
     * Get formatted position title for signatures
     */
    private static function getPositionTitle(User $user): string
    {
        // Map role to formal position title
        $titleMap = [
            'ketua' => 'Ketua Pengadilan Negeri Natuna',
            'sekretaris' => 'Sekretaris Pengadilan Negeri Natuna',
            'panitera' => 'Panitera Pengadilan Negeri Natuna',
        ];

        // Use mapped title if available, otherwise use jabatan field
        if (isset($titleMap[$user->role])) {
            return $titleMap[$user->role];
        }

        // Fallback to jabatan field with court name
        if ($user->jabatan) {
            return $user->jabatan . ', Pengadilan Negeri Natuna';
        }

        // Last resort
        return 'Pejabat Pengadilan Negeri Natuna';
    }

    /**
     * Export form using template-based approach for pixel-perfect accuracy
     */
    public static function exportFormPermintaanCutiTemplate(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['user', 'atasanReviewer', 'pejabat', 'user.atasan']);
        $user = $leaveRequest->user;
        $ketua = User::where('role', 'ketua')->first();

        // Load the template
        $templatePath = storage_path('app/templates/form-permintaan-cuti-template.docx');

        // Create a temporary working copy
        $tempFile = tempnam(sys_get_temp_dir(), 'form_cuti_');
        copy($templatePath, $tempFile);

        // Load with TemplateProcessor
        $template = new TemplateProcessor($tempFile);

        // Date formatting
        $bulanIndo = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                      7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $bulanRomawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $fmt = function($d) use ($bulanIndo) { return $d ? ($d->day . ' ' . $bulanIndo[$d->month] . ' ' . $d->year) : '...'; };

        // Nomor surat
        $bulanCuti = $bulanRomawi[$leaveRequest->created_at->month - 1];
        $tahunCuti = $leaveRequest->created_at->year;

        // Format lama cuti dengan kata "hari"
        $hari = $leaveRequest->total_hari_kerja ?? $leaveRequest->total_days;
        $lamaHari = $hari . ' hari';

        // Masa kerja
        $mk = '0 Tahun 0 Bulan';
        if ($user->masa_kerja_mulai) {
            $now = $leaveRequest->created_at ?? now();
            $y = (int) $user->masa_kerja_mulai->diffInYears($now);
            $m = (int) $user->masa_kerja_mulai->copy()->addYears($y)->diffInMonths($now);
            $mk = "{$y} Tahun {$m} Bulan";
        }

        // Catatan cuti
        $tahun = $leaveRequest->created_at->year;
        $cr = CutiRecord::where('user_id', $user->id)->where('tahun', $tahun)->first();
        $sisa = $cr ? $cr->sisa_cuti : $user->leave_balance;

        // Replace values in template
        $template->setValue('tanggal', $fmt($leaveRequest->created_at));
        $template->setValue('nomor_urut', '');
        $template->setValue('bulan_cuti', $bulanCuti);
        $template->setValue('tahun_cuti', $tahunCuti);
        $template->setValue('nama', $user->name);
        $template->setValue('nip', $user->nip);
        $template->setValue('jabatan', $user->jabatan ?? '-');
        $template->setValue('masa_kerja', $mk);
        $template->setValue('unit_kerja', $user->unit_kerja ?? 'Pengadilan Negeri Natuna');

        // Jenis cuti checkmarks
        $template->setValue('cuti_tahunan', $leaveRequest->type === LeaveRequest::TYPE_TAHUNAN ? '√' : '-');
        $template->setValue('cuti_besar', $leaveRequest->type === LeaveRequest::TYPE_BESAR ? '√' : '-');
        $template->setValue('cuti_sakit', $leaveRequest->type === LeaveRequest::TYPE_SAKIT ? '√' : '-');
        $template->setValue('cuti_melahirkan', $leaveRequest->type === LeaveRequest::TYPE_MELAHIRKAN ? '√' : '-');
        $template->setValue('cuti_alasan_penting', $leaveRequest->type === LeaveRequest::TYPE_ALASAN_PENTING ? '√' : '-');
        $template->setValue('cuti_luar_tanggungan', $leaveRequest->type === LeaveRequest::TYPE_LUAR_TANGGUNGAN ? '√' : '-');

        $template->setValue('alasan', $leaveRequest->reason);
        $template->setValue('lama_hari', $lamaHari);
        $template->setValue('tanggal_mulai', $fmt($leaveRequest->start_date));
        $template->setValue('tanggal_selesai', $fmt($leaveRequest->end_date));

        // Catatan cuti data
        $template->setValue('tahun_n2', $tahun - 2);
        $template->setValue('sisa_n2', '0');
        $template->setValue('keterangan_n2', 'Sisa 0');

        // Cascade deduction: carry-over dari tahun lalu habis dulu, baru potong tahun ini
        $carry_n1     = ($cr && $cr->carry_over > 0) ? (int)$cr->carry_over : 0;
        $hak_n        = $cr ? (int)$cr->hak_cuti : 12;
        $deduct_n1    = min($carry_n1, $hari);          // berapa dikurangi dari carry-over
        $deduct_n     = max(0, $hari - $deduct_n1);     // sisanya potong tahun ini

        $template->setValue('tahun_n1', $tahun - 1);
        $template->setValue('sisa_n1', (string)$carry_n1);
        $template->setValue('keterangan_n1', 'Sisa ' . ($carry_n1 - $deduct_n1));

        $template->setValue('tahun_n', $tahun);
        $template->setValue('sisa_n', (string)$hak_n);
        $template->setValue('keterangan_n', 'Sisa ' . max(0, $hak_n - $deduct_n));

        $template->setValue('alamat', $leaveRequest->alamat_cuti ?? $user->alamat ?? '');
        $template->setValue('telepon', $leaveRequest->telepon_cuti ?? $user->telepon ?? '');
        $template->setValue('nama_pemohon', $user->name);
        $template->setValue('nip_pemohon', $user->nip);

        // Atasan - pre-fill dari user->atasan jika belum di-review
        // Untuk alur skip (langsung ke Ketua), section VII dikosongkan
        if ($user->skipAtasanReview()) {
            $template->setValue('jabatan_atasan', '');
            $template->setValue('nama_atasan', '');
            $template->setValue('nip_atasan', '');
        } else {
            $atasan = $leaveRequest->atasanReviewer ?? $user->atasan;
            if ($atasan) {
                $template->setValue('jabatan_atasan', self::getPositionTitle($atasan));
                $template->setValue('nama_atasan', $atasan->name);
                $template->setValue('nip_atasan', $atasan->nip);
            } else {
                $template->setValue('jabatan_atasan', '');
                $template->setValue('nama_atasan', '');
                $template->setValue('nip_atasan', '');
            }
        }

        // Pejabat - selalu pre-fill dengan data Ketua
        $pejabat = $leaveRequest->pejabat ?? $ketua;
        if ($pejabat) {
            $template->setValue('jabatan_pejabat', self::getPositionTitle($pejabat));
            $template->setValue('nama_pejabat', $pejabat->name);
            $template->setValue('nip_pejabat', $pejabat->nip);
        } else {
            $template->setValue('jabatan_pejabat', '');
            $template->setValue('nama_pejabat', '');
            $template->setValue('nip_pejabat', '');
        }

        $filename = "Form-Cuti-{$user->nip}-{$leaveRequest->start_date->format('Ymd')}.docx";
        $outputFile = tempnam(sys_get_temp_dir(), 'form_output_');
        $template->saveAs($outputFile);

        return response()->download($outputFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Export Surat Permohonan Cuti sebagai DOCX (kertas folio/F4)
     */
    public static function exportSuratPermohonanDocx(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load('user');
        $user = $leaveRequest->user;
        $ketua = User::where('role', 'ketua')->first();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(12);

        // Folio F4: 21.5cm x 33cm, margin: atas/kanan/bawah 2cm, kiri 2.5cm
        $section = $phpWord->addSection([
            'pageSizeW'    => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(21.5),
            'pageSizeH'    => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(33),
            'marginTop'    => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2),
            'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2),
            'marginLeft'   => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2.5),
            'marginRight'  => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(2),
        ]);

        // Helper
        $bulanIndo = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
                      7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        $fmt = fn($d) => $d->day . ' ' . $bulanIndo[$d->month] . ' ' . $d->year;

        // Terbilang
        $satuan = ['','satu','dua','tiga','empat','lima','enam','tujuh','delapan','sembilan','sepuluh','sebelas'];
        $terbilangFn = function(int $n) use (&$terbilangFn, $satuan): string {
            $n = abs($n);
            if ($n < 12) return $satuan[$n];
            if ($n < 20) return $satuan[$n - 10] . ' belas';
            if ($n < 100) return $satuan[(int)($n/10)] . ' puluh' . ($n%10 ? ' '.$satuan[$n%10] : '');
            if ($n < 200) return 'seratus' . ($n-100 ? ' '.$terbilangFn($n-100) : '');
            return $satuan[(int)($n/100)] . ' ratus' . ($n%100 ? ' '.$terbilangFn($n%100) : '');
        };

        $hari = $leaveRequest->total_hari_kerja ?? $leaveRequest->total_days;
        $terbilang = $terbilangFn((int)$hari);

        // Alasan lengkap
        $alasan = $leaveRequest->reason;
        if ($leaveRequest->type === LeaveRequest::TYPE_ALASAN_PENTING && $leaveRequest->alasan_cap) {
            $capLabel = LeaveRequest::capLabels()[$leaveRequest->alasan_cap] ?? '';
            if ($capLabel) $alasan .= ' (' . strtolower($capLabel) . ')';
        }
        if ($leaveRequest->type === LeaveRequest::TYPE_MELAHIRKAN && $leaveRequest->kelahiran_ke) {
            $alasan .= ' (kelahiran anak ke-' . $leaveRequest->kelahiran_ke . ')';
        }

        $sapaanKetua = ($ketua && $ketua->jenis_kelamin === 'P') ? 'Ibu' : 'Bapak';

        $fnt  = ['name' => 'Times New Roman', 'size' => 12];
        $fntB = ['name' => 'Times New Roman', 'size' => 12, 'bold' => true];
        $ls   = ['lineHeight' => 1.8, 'spaceAfter' => 0, 'spaceBefore' => 0];
        $lsAf = ['lineHeight' => 1.8, 'spaceAfter' => 160, 'spaceBefore' => 0];

        // Tanggal (rata kanan)
        $section->addText('Natuna, ' . $fmt($leaveRequest->created_at), $fnt,
            array_merge($ls, ['alignment' => Jc::END, 'spaceAfter' => 320]));

        // Tujuan
        $section->addText('Kepada Yth.', $fnt, $ls);
        $section->addText($sapaanKetua . ' Ketua Pengadilan Negeri Natuna', $fnt, $ls);
        $section->addText('Di-', $fnt, $ls);
        $section->addText('        Ranai', $fnt, ['lineHeight' => 1.8, 'spaceAfter' => 320, 'spaceBefore' => 0]);

        // Perihal
        $perihalRun = $section->addTextRun(['lineHeight' => 1.8, 'spaceAfter' => 320, 'spaceBefore' => 0]);
        $perihalRun->addText('Perihal : ', $fnt);
        $perihalRun->addText('Permohonan ' . $leaveRequest->type_label, $fntB);

        // Pembuka
        $section->addText('Dengan hormat,', $fnt, $lsAf);

        // Yang bertanda tangan (rata kiri, sama dengan "Dengan hormat")
        $section->addText('Yang bertanda tangan di bawah ini:', $fnt, $lsAf);

        // Tabel data pemohon (tanpa indent)
        $tbl = $section->addTable([
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMarginTop' => 0,
            'cellMarginBottom' => 0,
            'cellMarginLeft' => 0,
            'cellMarginRight' => 0,
        ]);
        $sp = ['lineHeight' => 1.8, 'spaceAfter' => 0, 'spaceBefore' => 0];
        $rows = [
            ['Nama',               $user->name],
            ['NIP',                $user->nip],
            ['Pangkat/Gol. Ruang', $user->golongan_ruang ?? '-'],
            ['Jabatan',            $user->jabatan ?? '-'],
            ['Satuan Organisasi',  $user->unit_kerja ?? 'Pengadilan Negeri Natuna'],
        ];
        foreach ($rows as $row) {
            $tbl->addRow();
            $tbl->addCell(2900)->addText($row[0], $fnt, $sp);
            $tbl->addCell(300)->addText(':', $fnt, $sp);
            $tbl->addCell(7100)->addText($row[1], $fnt, $sp);
        }

        $section->addText('', $fnt, ['spaceAfter' => 160]);

        // Isi permohonan (rata kiri, sama dengan "Dengan hormat")
        $section->addText(
            'Dengan ini mengajukan permohonan ' . $leaveRequest->type_label .
            ' selama ' . $terbilang . ' (' . $hari . ') hari kerja, terhitung mulai tanggal ' .
            $fmt($leaveRequest->start_date) . ' sampai dengan tanggal ' .
            $fmt($leaveRequest->end_date) . ', dikarenakan ' . $alasan . '.',
            $fnt,
            ['lineHeight' => 1.8, 'spaceAfter' => 320, 'spaceBefore' => 0, 'alignment' => Jc::BOTH]
        );

        // Penutup (rata kiri, sama dengan "Dengan hormat")
        $section->addText(
            'Demikian permohonan ini saya buat untuk dapat dipertimbangkan sebagaimana mestinya.',
            $fnt,
            ['lineHeight' => 1.8, 'spaceAfter' => 640, 'spaceBefore' => 0, 'alignment' => Jc::BOTH]
        );

        // TTD — tabel 2 kolom: kiri kosong, kanan berisi tanda tangan (teks center)
        // agar posisi "Hormat saya" sama dengan PDF (center dalam kotak kanan)
        $ttdTbl = $section->addTable([
            'borderSize' => 0, 'borderColor' => 'FFFFFF',
            'cellMarginTop' => 0, 'cellMarginBottom' => 0,
            'cellMarginLeft' => 0, 'cellMarginRight' => 0,
        ]);
        $ttdTbl->addRow();
        $ttdTbl->addCell(5150); // kolom kiri kosong
        $ttdKanan = $ttdTbl->addCell(5150);
        $ttdKanan->addText('Hormat saya,', $fnt,
            ['alignment' => Jc::CENTER, 'spaceAfter' => 1200, 'spaceBefore' => 0, 'lineHeight' => 1.8]);
        $ttdTbl->addRow();
        $ttdTbl->addCell(5150);
        $ttdKanan2 = $ttdTbl->addCell(5150);
        $ttdKanan2->addText($user->name,
            array_merge($fntB, ['underline' => Font::UNDERLINE_SINGLE]),
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 1.8]);

        $filename = "Surat-Permohonan-Cuti-{$user->nip}-{$leaveRequest->start_date->format('Ymd')}.docx";
        $tempFile = tempnam(sys_get_temp_dir(), 'surat_cuti_');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    public static function exportFormPermintaanCuti(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['user', 'atasanReviewer', 'pejabat']);
        $user = $leaveRequest->user;
        $ketua = User::where('role', 'ketua')->first();

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Bookman Old Style');
        $phpWord->setDefaultFontSize(9);

        $section = $phpWord->addSection([
            'pageSizeW' => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(8.5),
            'pageSizeH' => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(14),
            'marginTop' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.0),
            'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(0.8),
            'marginLeft' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.5),
            'marginRight' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(1.5),
        ]);

        $bulanIndo = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                      7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
        $bulanRomawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
        $fmt = function($d) use ($bulanIndo) { return $d ? ($d->day . ' ' . $bulanIndo[$d->month] . ' ' . $d->year) : '...'; };

        $hari = $leaveRequest->total_hari_kerja ?? $leaveRequest->total_days;
        $nomor = "700/KPN.W32.U4/KP5.3/{$bulanRomawi[$leaveRequest->created_at->month - 1]}/{$leaveRequest->created_at->year}";

        // HEADER - MINIMAL SPACING
        $section->addText('LAMPIRAN II', ['name' => 'Bookman Old Style', 'size' => 7, 'bold' => true, 'underline' => Font::UNDERLINE_SINGLE], ['alignment' => Jc::END, 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('SURAT EDARAN SEKRETARIS MAHKAMAH AGUNG', ['name' => 'Bookman Old Style', 'size' => 7], ['alignment' => Jc::END, 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('REPUBLIK INDONESIA NOMOR 13 TAHUN 2019', ['name' => 'Bookman Old Style', 'size' => 7], ['alignment' => Jc::END, 'spaceAfter' => 30, 'spaceBefore' => 0, 'lineHeight' => 0.9]);

        $section->addText("Ranai, {$fmt($leaveRequest->created_at)}", ['name' => 'Bookman Old Style', 'size' => 9], ['alignment' => Jc::END, 'spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('Kepada Yth.', ['name' => 'Bookman Old Style', 'size' => 9], ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('Ketua Pengadilan Negeri Natuna', ['name' => 'Bookman Old Style', 'size' => 9], ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('Di-', ['name' => 'Bookman Old Style', 'size' => 9], ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText(' Ranai', ['name' => 'Bookman Old Style', 'size' => 9], ['spaceAfter' => 30, 'spaceBefore' => 0]);

        $section->addText('FORMULIR PERMINTAAN DAN PEMBERIAN CUTI', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true, 'underline' => Font::UNDERLINE_SINGLE], ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]);
        $section->addText("NOMOR: {$nomor}", ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 30, 'spaceBefore' => 0]);

        // DATA
        $mk = '0 Tahun 0 Bulan';
        if ($user->masa_kerja_mulai) {
            $now = $leaveRequest->created_at ?? now();
            $y = (int) $user->masa_kerja_mulai->diffInYears($now);
            $m = (int) $user->masa_kerja_mulai->copy()->addYears($y)->diffInMonths($now);
            $mk = "{$y} Tahun {$m} Bulan";
        }

        $tahun = $leaveRequest->created_at->year;
        $cr = CutiRecord::where('user_id', $user->id)->where('tahun', $tahun)->first();
        $sisa = $cr ? $cr->sisa_cuti : $user->leave_balance;

        // TABLE STYLE - EXTREME COMPACT with exact cell padding
        $ts = [
            'borderSize' => 3,
            'borderColor' => '000000',
            'cellMarginTop' => 1,
            'cellMarginBottom' => 1,
            'cellMarginLeft' => 10,
            'cellMarginRight' => 10,
            'width' => 10500,
            'unit' => 'dxa'
        ];
        $sp = ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.85];

        // I. DATA PEGAWAI
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500, ['gridSpan' => 4])->addText('I. DATA PEGAWAI', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], $sp);
        $t->addRow(180, ['exactHeight' => true]);
        $t->addCell(1400)->addText('Nama', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], $sp);
        $t->addCell(3700)->addText($user->name, ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $t->addCell(1000)->addText('NIP', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], $sp);
        $t->addCell(4400)->addText($user->nip, ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $t->addRow(180, ['exactHeight' => true]);
        $t->addCell(1400)->addText('Jabatan', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], $sp);
        $t->addCell(3700)->addText($user->jabatan ?? '-', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $t->addCell(1000)->addText('Masa Kerja', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], $sp);
        $t->addCell(4400)->addText($mk, ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $t->addRow(180, ['exactHeight' => true]);
        $t->addCell(1400)->addText('Unit Kerja', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], $sp);
        $t->addCell(9100, ['gridSpan' => 3])->addText($user->unit_kerja ?? 'Pengadilan Negeri Natuna', ['name' => 'Bookman Old Style', 'size' => 9], $sp);

        // II. JENIS CUTI
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500, ['gridSpan' => 2])->addText('II. JENIS CUTI YANG DIAMBIL **', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], $sp);
        $opts = [
            [LeaveRequest::TYPE_TAHUNAN, '1. Cuti Tahunan', LeaveRequest::TYPE_BESAR, '2. Cuti Besar'],
            [LeaveRequest::TYPE_SAKIT, '3. Cuti Sakit', LeaveRequest::TYPE_MELAHIRKAN, '4. Cuti Melahirkan'],
            [LeaveRequest::TYPE_ALASAN_PENTING, '5. Cuti Karena Alasan Penting', LeaveRequest::TYPE_LUAR_TANGGUNGAN, '6. Cuti di Luar Tanggungan Negara'],
        ];
        foreach ($opts as $p) {
            $t->addRow(170, ['exactHeight' => true]);
            $c1 = $t->addCell(5250);
            $r1 = $c1->addTextRun($sp);
            $r1->addText(($leaveRequest->type === $p[0] ? '√' : '-') . '  ' . $p[1], ['name' => 'Bookman Old Style', 'size' => 9]);
            $c2 = $t->addCell(5250);
            $r2 = $c2->addTextRun($sp);
            $r2->addText(($leaveRequest->type === $p[2] ? '√' : '-') . '  ' . $p[3], ['name' => 'Bookman Old Style', 'size' => 9]);
        }

        // III. ALASAN
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500)->addText('III. ALASAN CUTI', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], $sp);
        $t->addRow(250, ['exactHeight' => true]);
        $t->addCell(10500)->addText($leaveRequest->reason, ['name' => 'Bookman Old Style', 'size' => 9], $sp);

        // IV. LAMANYA
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500)->addText('IV. LAMANYA CUTI', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], $sp);
        $t->addRow(180, ['exactHeight' => true]);
        $t->addCell(1100)->addText('Selama', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $c = $t->addCell(2000);
        $r = $c->addTextRun($sp);
        $r->addText($hari . ' (hari/', ['name' => 'Bookman Old Style', 'size' => 9]);
        $r->addText('bulan', ['name' => 'Bookman Old Style', 'size' => 9, 'strikethrough' => true]);
        $r->addText('/', ['name' => 'Bookman Old Style', 'size' => 9]);
        $r->addText('tahun', ['name' => 'Bookman Old Style', 'size' => 9, 'strikethrough' => true]);
        $r->addText(')*', ['name' => 'Bookman Old Style', 'size' => 9]);
        $t->addCell(1500)->addText('Mulai Tanggal', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $t->addCell(2300)->addText($fmt($leaveRequest->start_date), ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $t->addCell(500)->addText('s/d', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $t->addCell(3100)->addText($fmt($leaveRequest->end_date), ['name' => 'Bookman Old Style', 'size' => 9], $sp);

        // V. CATATAN
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500)->addText('V. CATATAN CUTI ***', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], $sp);
        $t->addRow(170, ['exactHeight' => true]);
        $t->addCell(1700)->addText('1. CUTI TAHUNAN', ['name' => 'Bookman Old Style', 'size' => 8, 'bold' => true], $sp);
        $t->addCell(2200, ['gridSpan' => 2])->addText('PARAF PETUGAS CUTI', ['name' => 'Bookman Old Style', 'size' => 8, 'bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
        $t->addCell(6200)->addText('I. CUTI BESAR', ['name' => 'Bookman Old Style', 'size' => 8, 'bold' => true], $sp);
        $t->addCell(400)->addText('-', ['name' => 'Bookman Old Style', 'size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
        $t->addRow(150, ['exactHeight' => true]);
        $t->addCell(600)->addText('Tahun', ['name' => 'Bookman Old Style', 'size' => 8, 'bold' => true], $sp);
        $t->addCell(500)->addText('Sisa', ['name' => 'Bookman Old Style', 'size' => 8, 'bold' => true], $sp);
        $t->addCell(1600)->addText('Keterangan', ['name' => 'Bookman Old Style', 'size' => 8, 'bold' => true], $sp);
        $t->addCell(6200)->addText('II. CUTI SAKIT', ['name' => 'Bookman Old Style', 'size' => 8, 'bold' => true], $sp);
        $t->addCell(400)->addText('-', ['name' => 'Bookman Old Style', 'size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
        // Cascade: potong carry-over dulu, baru tahun ini
        $carry_n1b  = ($cr && $cr->carry_over > 0) ? (int)$cr->carry_over : 0;
        $hak_nb     = $cr ? (int)$cr->hak_cuti : 12;
        $deduct_n1b = min($carry_n1b, $hari);
        $deduct_nb  = max(0, $hari - $deduct_n1b);
        $dt = [
            [$tahun - 2, 0, 'Sisa 0', 'III. CUTI MELAHIRKAN'],
            [$tahun - 1, $carry_n1b, 'Sisa ' . ($carry_n1b - $deduct_n1b), 'IV. CUTI KARENA ALASAN PENTING'],
            [$tahun, $hak_nb, 'Sisa ' . max(0, $hak_nb - $deduct_nb), 'V. CUTI DILUAR TANGGUNGAN NEGARA'],
        ];
        foreach ($dt as $d) {
            $t->addRow(150, ['exactHeight' => true]);
            $t->addCell(600)->addText($d[0], ['name' => 'Bookman Old Style', 'size' => 8], $sp);
            $t->addCell(500)->addText($d[1], ['name' => 'Bookman Old Style', 'size' => 8], $sp);
            $t->addCell(1600)->addText($d[2], ['name' => 'Bookman Old Style', 'size' => 8], $sp);
            $t->addCell(6200)->addText($d[3], ['name' => 'Bookman Old Style', 'size' => 8, 'bold' => true], $sp);
            $t->addCell(400)->addText('-', ['name' => 'Bookman Old Style', 'size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
        }

        // VI. ALAMAT
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500)->addText('VI.ALAMAT SELAMA MENJALANKAN CUTI', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], $sp);
        $t->addRow(700, ['exactHeight' => true]);
        $cL = $t->addCell(5250);
        $cL->addText($leaveRequest->alamat_cuti ?? $user->alamat ?? '', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $cR = $t->addCell(5250);
        $cR->addText('TELP.', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $cR->addText($leaveRequest->telepon_cuti ?? $user->telepon ?? '', ['name' => 'Bookman Old Style', 'size' => 9], ['spaceAfter' => 20, 'lineHeight' => 0.85]);
        $cR->addText('Hormat Saya,', ['name' => 'Bookman Old Style', 'size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 80, 'lineHeight' => 0.85]);
        $cR->addText($user->name, ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true, 'underline' => Font::UNDERLINE_SINGLE], ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
        $cR->addText("NIP. {$user->nip}", ['name' => 'Bookman Old Style', 'size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);

        // VII. PERTIMBANGAN
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500)->addText('VII.PERTIMBANGAN ATASAN LANGSUNG **', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], $sp);
        $atasan = $leaveRequest->atasanReviewer;
        if ($user->skipAtasanReview() && !$atasan) {
            $t->addRow(600, ['exactHeight' => true]);
            $t->addCell(10500)->addText('LANGSUNG KE PEJABAT BERWENANG', ['name' => 'Bookman Old Style', 'size' => 8, 'italic' => true], ['alignment' => Jc::CENTER, 'lineHeight' => 0.85]);
        } else {
            $t->addRow(170, ['exactHeight' => true]);
            $t->addCell(2000)->addText('DISETUJUI', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
            $t->addCell(2300)->addText('PERUBAHAN****', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
            $t->addCell(2500)->addText('DITANGGUHKAN****', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
            $t->addCell(3700)->addText('TIDAK DISETUJUI ****', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
            $t->addRow(600, ['exactHeight' => true]);
            $c = $t->addCell(10500, ['gridSpan' => 4]);
            if ($atasan && $leaveRequest->reviewed_at) {
                $c->addText('Sekretaris Pengadilan Negeri Natuna,', ['name' => 'Bookman Old Style', 'size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 80, 'lineHeight' => 0.85]);
                $c->addText($atasan->name, ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true, 'underline' => Font::UNDERLINE_SINGLE], ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
                $c->addText("NIP. {$atasan->nip}", ['name' => 'Bookman Old Style', 'size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
            }
        }

        // VIII. KEPUTUSAN
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500)->addText('VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**', ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true], $sp);
        $t->addRow(170, ['exactHeight' => true]);
        $t->addCell(2000)->addText('DISETUJUI', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $t->addCell(2300)->addText('PERUBAHAN****', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $t->addCell(2500)->addText('DITANGGUHKAN****', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $t->addCell(3700)->addText('TIDAK DISETUJUI ****', ['name' => 'Bookman Old Style', 'size' => 9], $sp);
        $t->addRow(600, ['exactHeight' => true]);
        $c = $t->addCell(10500, ['gridSpan' => 4]);
        $pejabat = $leaveRequest->pejabat ?? $ketua;
        if ($pejabat && $leaveRequest->decided_at) {
            $c->addText('Ketua Pengadilan Negeri Natuna,', ['name' => 'Bookman Old Style', 'size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 80, 'lineHeight' => 0.85]);
            $c->addText($pejabat->name, ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true, 'underline' => Font::UNDERLINE_SINGLE], ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
            $c->addText("NIP. {$pejabat->nip}", ['name' => 'Bookman Old Style', 'size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
        }

        // FOOTER
        $section->addText('Catatan :', ['name' => 'Bookman Old Style', 'size' => 7], ['spaceAfter' => 0, 'spaceBefore' => 20, 'lineHeight' => 0.9]);
        $section->addText(' Coret yang tidak perlu.', ['name' => 'Bookman Old Style', 'size' => 7], ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('** Pilih salah satu dengan memberi tanda centang (√).', ['name' => 'Bookman Old Style', 'size' => 7], ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('*** diisi oleh pejabat yang menangani bidang kepegawaian sebelum PNS mengajukan cuti.', ['name' => 'Bookman Old Style', 'size' => 7], ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('**** diberi tanda centang dan alasannya.', ['name' => 'Bookman Old Style', 'size' => 7], ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('N = Cuti tahun berjalan.', ['name' => 'Bookman Old Style', 'size' => 7], ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('N-1 = Sisa cuti 1 tahun sebelumnya.', ['name' => 'Bookman Old Style', 'size' => 7], ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('N-2 = Sisa cuti 2 tahun sebelumnya.', ['name' => 'Bookman Old Style', 'size' => 7], ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);

        $filename = "Form-Cuti-{$user->nip}-{$leaveRequest->start_date->format('Ymd')}.docx";
        $tempFile = tempnam(sys_get_temp_dir(), 'form_cuti_');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
