<?php

/**
 * Create template v2 - more careful replacement with context
 */

require __DIR__ . '/vendor/autoload.php';

$originalFile = 'D:\\12. CUTI DESEMBER 2025\\4. CUTI TAHUNAN CANDRA TGL 15 DESEMBER 2025 - 2 JANUARI 2026\\FORM PERMINTAAN CUTI.docx';
$templateFile = __DIR__ . '/storage/app/templates/form-permintaan-cuti-template.docx';

// Copy original
copy($originalFile, $templateFile);
echo "Template copied from original\n";

$zip = new ZipArchive();
if ($zip->open($templateFile) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');

    if ($xml !== false) {
        // More targeted replacements using XML context
        // Be very specific to avoid wrong replacements

        // Header tanggal
        $xml = str_replace('Ranai, 9 Desember 2025', 'Ranai, ${tanggal}', $xml);
        $xml = str_replace('700/KPN.W32.U4/KP5.3/XII/2025', '${nomor}', $xml);

        // Data Pegawai - replace with exact XML text patterns
        $xml = str_replace('CANDRA FIRMANSYAH, S.I.Pust.', '${nama}', $xml);
        $xml = str_replace('>199312102020121001<', '>${nip}<', $xml);
        $xml = str_replace('Operator - Penata Layanan Operasional, Subbagian Kepegawaian, Organisasi, dan Tata Laksana', '${jabatan}', $xml);
        $xml = str_replace('5 Tahun 0 Bulan', '${masa_kerja}', $xml);

        // Jenis cuti - don't replace the checkbox symbols
        // They are already correct in the template

        // Alasan
        $xml = str_replace('Keperluan Keluarga', '${alasan}', $xml);

        // Lamanya - be careful with "12" as it appears in multiple places
        // Replace only in the specific context
        $xml = preg_replace('/Selama<\/w:t><\/w:r>.*?<w:t>12</', 'Selama</w:t></w:r><w:r><w:t>${lama_hari}<', $xml);

        // Tanggal mulai/selesai
        $xml = str_replace('>15 Desember 2025<', '>${tanggal_mulai}<', $xml);
        $xml = str_replace('>2 Januari 2026<', '>${tanggal_selesai}<', $xml);

        // Catatan Cuti - replace in table context
        // N-2 row: 2023, 0, "Sisa 0"
        $xml = preg_replace('/>2023<\/w:t>/', '>${tahun_n2}</w:t>', $xml, 1);
        // First occurrence of >0< after 2023
        $xml = preg_replace('/(>2023<.*?>)0(<)/', '$1${sisa_n2}$2', $xml, 1);

        // N-1 row: 2024, 6, "Sisa 0"
        $xml = preg_replace('/>2024<\/w:t>/', '>${tahun_n1}</w:t>', $xml, 1);
        $xml = preg_replace('/(>2024<.*?>)6(<)/', '$1${sisa_n1}$2', $xml, 1);

        // N row: 2025, 12, "Sisa 6"
        $xml = preg_replace('/>2025<\/w:t>/', '>${tahun_n}</w:t>', $xml, 1);
        $xml = preg_replace('/(>2025<.*?>)12(<)/', '$1${sisa_n}$2', $xml, 1);
        $xml = str_replace('>Sisa 6<', '>${keterangan_n}<', $xml);

        // Alamat & Telepon
        $xml = str_replace('Jalan LK 1 Pringsewu Utara RT/RW 005/002 Kelurahan Pringsewu Utara Kecamatan Pringsewu Kabupaten Pringsewu Propinsi Lampung', '${alamat}', $xml);
        $xml = str_replace('>0813-7387-2683<', '>${telepon}<', $xml);

        // Signature pemohon (appears twice, will replace both)
        // First in "Hormat Saya" section, second in NIP line
        // Replace as one block
        $xml = preg_replace(
            '/>CANDRA FIRMANSYAH, S\.I\.Pust\.<.*?> NIP\. <\/w:t><\/w:r>.*?<w:t>199312102020121001</s',
            '>${nama_pemohon}< $1 NIP. </w:t></w:r><w:r><w:t>${nip_pemohon}<',
            $xml
        );

        // Atasan
        $xml = str_replace('MARIO TYSON NADAPDAP, S.E.', '${nama_atasan}', $xml);
        $xml = str_replace('>199608172019031002<', '>${nip_atasan}<', $xml);

        // Pejabat
        $xml = str_replace('LODEWYK IVANDRIE SIMANJUNTAK, S.H., M.H.', '${nama_pejabat}', $xml);
        $xml = str_replace('>197511172001121003<', '>${nip_pejabat}<', $xml);

        // Set page size to Legal (12240 x 20160 twips)
        $xml = preg_replace('/<w:pgSz[^>]*\/>/', '<w:pgSz w:w="12240" w:h="20160"/>', $xml);
        if (strpos($xml, '<w:pgSz') === false) {
            // Add if not exists
            $xml = preg_replace('/(<w:sectPr[^>]*>)/', '$1<w:pgSz w:w="12240" w:h="20160"/>', $xml, 1);
        }

        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $xml);

        echo "✓ Replacements applied\n";
        echo "✓ Page size set to Legal\n";
    }

    $zip->close();
    echo "✓ Template created: {$templateFile}\n";
} else {
    echo "✗ Failed to open template\n";
}
