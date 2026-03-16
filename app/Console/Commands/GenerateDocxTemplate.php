<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\Shared\Converter;

class GenerateDocxTemplate extends Command
{
    protected $signature   = 'docx:generate-template';
    protected $description = 'Generate the form-permintaan-cuti DOCX template with placeholders';

    public function handle(): int
    {
        $dir = storage_path('app/templates');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            $this->info("Created directory: {$dir}");
        }

        $dest = $dir . '/form-permintaan-cuti-template.docx';
        $this->generateTemplate($dest);
        $this->info("Template generated: {$dest}");
        return Command::SUCCESS;
    }

    private function generateTemplate(string $dest): void
    {
        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Bookman Old Style');
        $phpWord->setDefaultFontSize(9);

        $section = $phpWord->addSection([
            'pageSizeW'    => Converter::inchToTwip(8.5),
            'pageSizeH'    => Converter::inchToTwip(14),
            'marginTop'    => Converter::cmToTwip(1.0),
            'marginBottom' => Converter::cmToTwip(0.8),
            'marginLeft'   => Converter::cmToTwip(1.5),
            'marginRight'  => Converter::cmToTwip(1.5),
        ]);

        $fnt  = ['name' => 'Bookman Old Style', 'size' => 9];
        $fntB = ['name' => 'Bookman Old Style', 'size' => 9, 'bold' => true];
        $fnt7 = ['name' => 'Bookman Old Style', 'size' => 7];
        $fnt7B = ['name' => 'Bookman Old Style', 'size' => 7, 'bold' => true, 'underline' => Font::UNDERLINE_SINGLE];
        $fnt8 = ['name' => 'Bookman Old Style', 'size' => 8];
        $fnt8B = ['name' => 'Bookman Old Style', 'size' => 8, 'bold' => true];
        $sp   = ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.85];
        $spE  = ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9];

        // Top-right: LAMPIRAN II
        $section->addText('LAMPIRAN II', $fnt7B, array_merge($spE, ['alignment' => Jc::END]));
        $section->addText('SURAT EDARAN SEKRETARIS MAHKAMAH AGUNG', $fnt7, array_merge($spE, ['alignment' => Jc::END]));
        $section->addText('REPUBLIK INDONESIA NOMOR 13 TAHUN 2019', $fnt7, array_merge($spE, ['alignment' => Jc::END, 'spaceAfter' => 30]));

        // Date + Recipient
        $section->addText('Ranai, ${tanggal}', $fnt, array_merge($spE, ['alignment' => Jc::END]));
        $section->addText('Kepada Yth.', $fnt, $spE);
        $section->addText('Ketua Pengadilan Negeri Natuna', $fnt, $spE);
        $section->addText('Di-', $fnt, $spE);
        $section->addText(' Ranai', $fnt, array_merge($spE, ['spaceAfter' => 30]));

        // Title
        $section->addText(
            'FORMULIR PERMINTAAN DAN PEMBERIAN CUTI',
            array_merge($fntB, ['underline' => Font::UNDERLINE_SINGLE]),
            ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0]
        );
        $section->addText(
            'NOMOR: 700/KPN.W32.U4/KP5.3/${bulan_cuti}/${tahun_cuti}',
            $fntB,
            ['alignment' => Jc::CENTER, 'spaceAfter' => 30, 'spaceBefore' => 0]
        );

        $ts = [
            'borderSize'        => 3,
            'borderColor'       => '000000',
            'cellMarginTop'     => 1,
            'cellMarginBottom'  => 1,
            'cellMarginLeft'    => 10,
            'cellMarginRight'   => 10,
            'width'             => 10500,
            'unit'              => 'dxa',
        ];

        // I. DATA PEGAWAI
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500, ['gridSpan' => 4])->addText('I. DATA PEGAWAI', $fntB, $sp);

        $t->addRow(180, ['exactHeight' => true]);
        $t->addCell(1400)->addText('Nama', $fntB, $sp);
        $t->addCell(3700)->addText('${nama}', $fnt, $sp);
        $t->addCell(1000)->addText('NIP', $fntB, $sp);
        $t->addCell(4400)->addText('${nip}', $fnt, $sp);

        $t->addRow(180, ['exactHeight' => true]);
        $t->addCell(1400)->addText('Jabatan', $fntB, $sp);
        $t->addCell(3700)->addText('${jabatan}', $fnt, $sp);
        $t->addCell(1000)->addText('Masa Kerja', $fntB, $sp);
        $t->addCell(4400)->addText('${masa_kerja}', $fnt, $sp);

        $t->addRow(180, ['exactHeight' => true]);
        $t->addCell(1400)->addText('Unit Kerja', $fntB, $sp);
        $t->addCell(9100, ['gridSpan' => 3])->addText('${unit_kerja}', $fnt, $sp);

        // II. JENIS CUTI
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500, ['gridSpan' => 2])->addText('II. JENIS CUTI YANG DIAMBIL **', $fntB, $sp);

        $pairs = [
            ['${cuti_tahunan}', '1. Cuti Tahunan',              '${cuti_besar}',          '2. Cuti Besar'],
            ['${cuti_sakit}',   '3. Cuti Sakit',                '${cuti_melahirkan}',     '4. Cuti Melahirkan'],
            ['${cuti_alasan_penting}', '5. Cuti Karena Alasan Penting', '${cuti_luar_tanggungan}', '6. Cuti di Luar Tanggungan Negara'],
        ];
        foreach ($pairs as $p) {
            $t->addRow(170, ['exactHeight' => true]);
            $c1 = $t->addCell(5250);
            $r1 = $c1->addTextRun($sp);
            $r1->addText($p[0] . '  ' . $p[1], $fnt);
            $c2 = $t->addCell(5250);
            $r2 = $c2->addTextRun($sp);
            $r2->addText($p[2] . '  ' . $p[3], $fnt);
        }

        // III. ALASAN
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500)->addText('III. ALASAN CUTI', $fntB, $sp);
        $t->addRow(250, ['exactHeight' => true]);
        $t->addCell(10500)->addText('${alasan}', $fnt, $sp);

        // IV. LAMANYA
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500)->addText('IV. LAMANYA CUTI', $fntB, $sp);
        $t->addRow(180, ['exactHeight' => true]);
        $t->addCell(1100)->addText('Selama', $fnt, $sp);
        $c = $t->addCell(2000);
        $r = $c->addTextRun($sp);
        $r->addText('${lama_hari} (hari/', $fnt);
        $r->addText('bulan', array_merge($fnt, ['strikethrough' => true]));
        $r->addText('/', $fnt);
        $r->addText('tahun', array_merge($fnt, ['strikethrough' => true]));
        $r->addText(')*', $fnt);
        $t->addCell(1500)->addText('Mulai Tanggal', $fnt, $sp);
        $t->addCell(2300)->addText('${tanggal_mulai}', $fnt, $sp);
        $t->addCell(500)->addText('s/d', $fnt, $sp);
        $t->addCell(3100)->addText('${tanggal_selesai}', $fnt, $sp);

        // V. CATATAN CUTI
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500)->addText('V. CATATAN CUTI ***', $fntB, $sp);

        $t->addRow(170, ['exactHeight' => true]);
        $t->addCell(1700)->addText('1. CUTI TAHUNAN', $fnt8B, $sp);
        $t->addCell(2200, ['gridSpan' => 2])->addText('PARAF PETUGAS CUTI', $fnt8B, ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
        $t->addCell(6200)->addText('I. CUTI BESAR', $fnt8B, $sp);
        $t->addCell(400)->addText('-', $fnt8, ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);

        $t->addRow(150, ['exactHeight' => true]);
        $t->addCell(600)->addText('Tahun', $fnt8B, $sp);
        $t->addCell(500)->addText('Sisa', $fnt8B, $sp);
        $t->addCell(1600)->addText('Keterangan', $fnt8B, $sp);
        $t->addCell(6200)->addText('II. CUTI SAKIT', $fnt8B, $sp);
        $t->addCell(400)->addText('-', $fnt8, ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);

        $catatanRows = [
            ['${tahun_n2}', '${sisa_n2}', '${keterangan_n2}', 'III. CUTI MELAHIRKAN'],
            ['${tahun_n1}', '${sisa_n1}', '${keterangan_n1}', 'IV. CUTI KARENA ALASAN PENTING'],
            ['${tahun_n}',  '${sisa_n}',  '${keterangan_n}',  'V. CUTI DILUAR TANGGUNGAN NEGARA'],
        ];
        foreach ($catatanRows as $row) {
            $t->addRow(150, ['exactHeight' => true]);
            $t->addCell(600)->addText($row[0], $fnt8, $sp);
            $t->addCell(500)->addText($row[1], $fnt8, $sp);
            $t->addCell(1600)->addText($row[2], $fnt8, $sp);
            $t->addCell(6200)->addText($row[3], $fnt8B, $sp);
            $t->addCell(400)->addText('-', $fnt8, ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
        }

        // VI. ALAMAT
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500)->addText('VI.ALAMAT SELAMA MENJALANKAN CUTI', $fntB, $sp);
        $t->addRow(700, ['exactHeight' => true]);
        $cL = $t->addCell(5250);
        $cL->addText('${alamat}', $fnt, $sp);
        $cR = $t->addCell(5250);
        $cR->addText('TELP.', $fnt, $sp);
        $cR->addText('${telepon}', $fnt, ['spaceAfter' => 20, 'lineHeight' => 0.85]);
        $cR->addText('Hormat Saya,', $fnt, ['alignment' => Jc::CENTER, 'spaceAfter' => 80, 'lineHeight' => 0.85]);
        $cR->addText('${nama_pemohon}', array_merge($fntB, ['underline' => Font::UNDERLINE_SINGLE]), ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
        $cR->addText('NIP. ${nip_pemohon}', $fnt, ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);

        // VII. PERTIMBANGAN ATASAN
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500)->addText('VII.PERTIMBANGAN ATASAN LANGSUNG **', $fntB, $sp);
        $t->addRow(170, ['exactHeight' => true]);
        $t->addCell(2000)->addText('DISETUJUI', $fnt, $sp);
        $t->addCell(2300)->addText('PERUBAHAN****', $fnt, $sp);
        $t->addCell(2500)->addText('DITANGGUHKAN****', $fnt, $sp);
        $t->addCell(3700)->addText('TIDAK DISETUJUI ****', $fnt, $sp);
        $t->addRow(600, ['exactHeight' => true]);
        $c = $t->addCell(10500, ['gridSpan' => 4]);
        $c->addText('${jabatan_atasan}', $fnt, ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
        $c->addText('${nama_atasan}', array_merge($fntB, ['underline' => Font::UNDERLINE_SINGLE]), ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
        $c->addText('NIP. ${nip_atasan}', $fnt, ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);

        // VIII. KEPUTUSAN PEJABAT
        $t = $section->addTable($ts);
        $t->addRow(200, ['exactHeight' => true]);
        $t->addCell(10500)->addText('VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI**', $fntB, $sp);
        $t->addRow(170, ['exactHeight' => true]);
        $t->addCell(2000)->addText('DISETUJUI', $fnt, $sp);
        $t->addCell(2300)->addText('PERUBAHAN****', $fnt, $sp);
        $t->addCell(2500)->addText('DITANGGUHKAN****', $fnt, $sp);
        $t->addCell(3700)->addText('TIDAK DISETUJUI ****', $fnt, $sp);
        $t->addRow(600, ['exactHeight' => true]);
        $c = $t->addCell(10500, ['gridSpan' => 4]);
        $c->addText('${jabatan_pejabat}', $fnt, ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
        $c->addText('${nama_pejabat}', array_merge($fntB, ['underline' => Font::UNDERLINE_SINGLE]), ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);
        $c->addText('NIP. ${nip_pejabat}', $fnt, ['alignment' => Jc::CENTER, 'spaceAfter' => 0, 'lineHeight' => 0.85]);

        // Footer notes
        $section->addText('Catatan :', $fnt7, ['spaceAfter' => 0, 'spaceBefore' => 20, 'lineHeight' => 0.9]);
        $section->addText('* Coret yang tidak perlu.', $fnt7, ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('** Pilih salah satu dengan memberi tanda centang (√).', $fnt7, ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('*** diisi oleh pejabat yang menangani bidang kepegawaian sebelum PNS mengajukan cuti.', $fnt7, ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('**** diberi tanda centang dan alasannya.', $fnt7, ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('N = Cuti tahun berjalan.', $fnt7, ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('N-1 = Sisa cuti 1 tahun sebelumnya.', $fnt7, ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);
        $section->addText('N-2 = Sisa cuti 2 tahun sebelumnya.', $fnt7, ['spaceAfter' => 0, 'spaceBefore' => 0, 'lineHeight' => 0.9]);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($dest);
    }
}
