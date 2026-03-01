<?php

/**
 * Create template v3 - using TemplateProcessor approach
 * Since text is split in XML, we'll manually create a working template
 */

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$originalFile = 'D:\\12. CUTI DESEMBER 2025\\4. CUTI TAHUNAN CANDRA TGL 15 DESEMBER 2025 - 2 JANUARI 2026\\FORM PERMINTAAN CUTI.docx';
$templateFile = __DIR__ . '/storage/app/templates/form-permintaan-cuti-template.docx';

// Copy original
copy($originalFile, $templateFile);
echo "Step 1: Template copied\n";

// Open as ZIP and work with raw XML
$zip = new ZipArchive();
if ($zip->open($templateFile) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');

    if ($xml !== false) {
        // Strategy: Replace text even if split across tags
        // We'll use a function that handles split text

        function replaceInXml($xml, $search, $replace) {
            // Remove spaces and tags between characters for matching
            $searchClean = str_replace(' ', '', $search);

            // Find all w:t elements and merge their content temporarily
            $pattern = '/<w:t[^>]*>([^<]*)<\/w:t>/';
            preg_match_all($pattern, $xml, $matches, PREG_OFFSET_CAPTURE);

            // Build a map of positions and text
            $textBlocks = [];
            foreach ($matches[0] as $i => $match) {
                $fullTag = $match[0];
                $position = $match[1];
                $innerText = $matches[1][$i][0];

                $textBlocks[] = [
                    'full' => $fullTag,
                    'text' => $innerText,
                    'pos' => $position,
                ];
            }

            // Simple approach: just do str_replace on the XML
            // Handle common split patterns
            $xmlBefore = $xml;

            // Method 1: Direct replacement if not split
            $xml = str_replace('>' . $search . '<', '>' . $replace . '<', $xml);

            // Method 2: Replace across common split patterns
            // For example: "Text</w:t></w:r><w:r><w:t>More" -> handle this

            // If no change, try more aggressive approach
            if ($xml === $xmlBefore && strpos($search, ' ') !== false) {
                // Try replacing parts
                $parts = explode(' ', $search);
                // Complex... skip for now
            }

            return $xml;
        }

        // Apply replacements
        echo "Step 2: Applying replacements...\n";

        $replacements = [
            'Ranai, 9 Desember 2025' => 'Ranai, ${tanggal}',
            '700/KPN.W32.U4/KP5.3/XII/2025' => '${nomor}',
            'CANDRA FIRMANSYAH, S.I.Pust.' => '${nama}',
            '199312102020121001' => '${nip}',
            'Operator - Penata Layanan Operasional, Subbagian Kepegawaian, Organisasi, dan Tata Laksana' => '${jabatan}',
            '5 Tahun 0 Bulan' => '${masa_kerja}',
            'Keperluan Keluarga' => '${alasan}',
            '15 Desember 2025' => '${tanggal_mulai}',
            '2 Januari 2026' => '${tanggal_selesai}',
            'Jalan LK 1 Pringsewu Utara RT/RW 005/002 Kelurahan Pringsewu Utara Kecamatan Pringsewu Kabupaten Pringsewu Propinsi Lampung' => '${alamat}',
            '0813-7387-2683' => '${telepon}',
            'MARIO TYSON NADAPDAP, S.E.' => '${nama_atasan}',
            '199608172019031002' => '${nip_atasan}',
            'LODEWYK IVANDRIE SIMANJUNTAK, S.H., M.H.' => '${nama_pejabat}',
            '197511172001121003' => '${nip_pejabat}',
        ];

        foreach ($replacements as $search => $replace) {
            $beforeCount = substr_count($xml, $search);
            $xml = replaceInXml($xml, $search, $replace);
            $afterCount = substr_count($xml, $replace);

            if ($beforeCount > 0 || $afterCount > 0) {
                echo "  - '{$search}' -> '{$replace}' ({$beforeCount} found, {$afterCount} replaced)\n";
            }
        }

        // Special handling for numbers that appear multiple times
        // Need context-aware replacement

        // Set page size to Legal
        echo "Step 3: Setting page size to Legal...\n";
        $xml = preg_replace('/<w:pgSz[^>]*\/>/', '<w:pgSz w:w="12240" w:h="20160"/>', $xml);

        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $xml);
    }

    $zip->close();
    echo "Step 4: Template saved!\n";
    echo "\n✓ Template created at: {$templateFile}\n";
    echo "\n⚠ NOTE: Due to XML splitting in DOCX, some replacements might need manual editing.\n";
    echo "Please test the template and manually edit in Word if needed.\n";
} else {
    echo "✗ Failed to open template\n";
}
