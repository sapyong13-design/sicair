<?php

require __DIR__ . '/vendor/autoload.php';

$file = 'D:\\12. CUTI DESEMBER 2025\\4. CUTI TAHUNAN CANDRA TGL 15 DESEMBER 2025 - 2 JANUARI 2026\\FORM PERMINTAAN CUTI.docx';

$zip = new ZipArchive();
if ($zip->open($file) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();

    if ($xml) {
        // Remove XML tags to show text content
        $text = strip_tags($xml);

        // Clean up whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        echo "=== EXTRACTED TEXT ===\n\n";
        echo $text;
        echo "\n\n=== RAW XML (first 5000 chars) ===\n\n";
        echo substr($xml, 0, 5000);
    }
} else {
    echo "Failed to open DOCX\n";
}
