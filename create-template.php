<?php

/**
 * Script untuk membuat template dari DOCX asli
 * Mengganti data spesifik dengan placeholder untuk TemplateProcessor
 */

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

$originalFile = 'D:\\12. CUTI DESEMBER 2025\\4. CUTI TAHUNAN CANDRA TGL 15 DESEMBER 2025 - 2 JANUARI 2026\\FORM PERMINTAAN CUTI.docx';
$templateFile = __DIR__ . '/storage/app/templates/form-permintaan-cuti-template.docx';

// Mapping data asli ke placeholder
$replacements = [
    // Header
    'Ranai, 9 Desember 2025' => 'Ranai, ${tanggal}',
    '700/KPN.W32.U4/KP5.3/XII/2025' => '${nomor}',

    // Data Pegawai
    'CANDRA FIRMANSYAH, S.I.Pust.' => '${nama}',
    '199312102020121001' => '${nip}',
    'Operator - Penata Layanan Operasional, Subbagian Kepegawaian, Organisasi, dan Tata Laksana' => '${jabatan}',
    '5 Tahun 0 Bulan' => '${masa_kerja}',

    // Alasan
    'Keperluan Keluarga' => '${alasan}',

    // Lamanya
    '12' => '${lama_hari}',
    '15 Desember 2025' => '${tanggal_mulai}',
    '2 Januari 2026' => '${tanggal_selesai}',

    // Catatan Cuti
    '2023' => '${tahun_n2}',
    '2024' => '${tahun_n1}',
    '2025' => '${tahun_n}',
    'Sisa 6' => '${keterangan_n}',

    // Alamat & Kontak
    'Jalan LK 1 Pringsewu Utara RT/RW 005/002 Kelurahan Pringsewu Utara Kecamatan Pringsewu Kabupaten Pringsewu Propinsi Lampung' => '${alamat}',
    '0813-7387-2683' => '${telepon}',

    // Tanda tangan pemohon (muncul 2x - di VI dan signature)
    'CANDRA FIRMANSYAH, S.I.Pust. NIP. 199312102020121001' => '${nama_pemohon}
NIP. ${nip_pemohon}',

    // Atasan
    'MARIO TYSON NADAPDAP, S.E.' => '${nama_atasan}',
    '199608172019031002' => '${nip_atasan}',

    // Pejabat
    'LODEWYK IVANDRIE SIMANJUNTAK, S.H., M.H.' => '${nama_pejabat}',
    '197511172001121003' => '${nip_pejabat}',

    // Checkbox - harus order dari yang spesifik ke general
    'Cuti Tahunan√' => '${cuti_tahunan}  Cuti Tahunan',
    'Cuti Besar-' => '${cuti_besar}  Cuti Besar',
    'Cuti Sakit-' => '${cuti_sakit}  Cuti Sakit',
    'Cuti Melahirkan-' => '${cuti_melahirkan}  Cuti Melahirkan',
    'Cuti Karena Alasan Penting-' => '${cuti_alasan_penting}  Cuti Karena Alasan Penting',
    'Cuti di Luar Tanggungan Negara-' => '${cuti_luar_tanggungan}  Cuti di Luar Tanggungan Negara',

    // Catatan cuti sisa values - more specific patterns
    '0Sisa 0' => '${sisa_n2}Sisa ${sisa_n2}',  // N-2 row
    '6Sisa 0' => '${sisa_n1}Sisa ${sisa_n1}',  // N-1 row
    '12Sisa 6' => '${sisa_n}${keterangan_n}',  // N row
];

echo "Membaca file asli...\n";

// Untuk DOCX, kita perlu menggunakan approach berbeda
// Karena PhpWord tidak bisa langsung search-replace di existing file,
// kita akan copy file dan menggunakan TemplateProcessor
copy($originalFile, $templateFile);

echo "Template disalin ke: {$templateFile}\n";
echo "\nCATATAN PENTING:\n";
echo "File template telah disalin. Anda perlu mengedit MANUAL menggunakan Microsoft Word:\n\n";
echo "1. Buka file: {$templateFile}\n";
echo "2. Ganti data berikut dengan placeholder (copy-paste exact):\n\n";

foreach ($replacements as $old => $new) {
    echo "   Cari: \"{$old}\"\n";
    echo "   Ganti: \"{$new}\"\n\n";
}

echo "\n3. Untuk checkbox cuti, ganti dengan:\n";
echo "   √  → \${cuti_tahunan}\n";
echo "   -  → \${cuti_besar}\n";
echo "   dst. sesuai jenis cuti\n\n";

echo "4. Save file setelah semua diganti\n";
echo "\nATAU gunakan approach otomatis dengan ZIP manipulation...\n";

// Alternative: Unzip, replace in XML, rezip
echo "\nMencoba approach otomatis...\n";

$zip = new ZipArchive();
if ($zip->open($templateFile) === TRUE) {
    // DOCX main document is in word/document.xml
    $content = $zip->getFromName('word/document.xml');

    if ($content !== false) {
        // Replace dalam XML
        foreach ($replacements as $old => $new) {
            $content = str_replace($old, $new, $content);
        }

        // Replace checkbox symbols jika ada
        // Note: simbol √ mungkin ter-encode berbeda di XML

        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $content);

        echo "✓ Replacements applied to document.xml\n";
    }

    $zip->close();
    echo "✓ Template created successfully!\n";
} else {
    echo "✗ Failed to open DOCX as ZIP\n";
}

echo "\nTemplate siap digunakan di: {$templateFile}\n";
