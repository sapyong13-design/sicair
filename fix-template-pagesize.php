<?php

/**
 * Fix template: set page size to Legal
 */

require __DIR__ . '/vendor/autoload.php';

$templateFile = __DIR__ . '/storage/app/templates/form-permintaan-cuti-template.docx';

echo "Opening template: {$templateFile}\n";

$zip = new ZipArchive();
if ($zip->open($templateFile) === TRUE) {
    // Read document.xml.rels to understand structure
    $docXml = $zip->getFromName('word/document.xml');

    // Read settings to modify page size
    $settingsXml = $zip->getFromName('word/settings.xml');

    // Most important: modify section properties in document.xml
    // Legal size = 612 x 1008 points = 12240 x 20160 twips

    if ($docXml !== false) {
        // Find sectPr (section properties) and update page size
        // Legal: width=12240 twips, height=20160 twips

        // Pattern for existing page size
        $pattern = '/<w:pgSz[^>]*w:w="(\d+)"[^>]*w:h="(\d+)"[^>]*\/>/';

        if (preg_match($pattern, $docXml, $matches)) {
            $currentW = $matches[1];
            $currentH = $matches[2];
            echo "Current page size: {$currentW} x {$currentH} twips\n";

            // Replace with Legal size
            $docXml = preg_replace(
                '/<w:pgSz[^>]*\/>/',
                '<w:pgSz w:w="12240" w:h="20160"/>',
                $docXml
            );

            echo "✓ Updated to Legal size (12240 x 20160 twips)\n";
        } else {
            echo "⚠ Page size tag not found, trying alternative pattern\n";

            // Alternative: add page size to sectPr if not exists
            $docXml = preg_replace(
                '/(<w:sectPr[^>]*>)/',
                '$1<w:pgSz w:w="12240" w:h="20160"/>',
                $docXml,
                1
            );
        }

        // Save back
        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $docXml);
    }

    $zip->close();
    echo "✓ Template updated successfully!\n";
} else {
    echo "✗ Failed to open template\n";
}

echo "\nDone!\n";
