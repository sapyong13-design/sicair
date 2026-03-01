<?php

require __DIR__ . '/vendor/autoload.php';

$templateFile = __DIR__ . '/storage/app/templates/form-permintaan-cuti-template.docx';

$zip = new ZipArchive();
if ($zip->open($templateFile) === TRUE) {
    $xml = $zip->getFromName('word/document.xml');
    $zip->close();

    if ($xml) {
        // Extract text content
        $text = strip_tags($xml);
        $text = preg_replace('/\s+/', ' ', $text);

        echo "=== TEMPLATE TEXT CONTENT ===\n\n";
        echo $text;
        echo "\n\n=== PLACEHOLDERS FOUND ===\n\n";

        // Find all placeholders
        preg_match_all('/\\\$\\\{[^}]+\\\}/', $text, $matches);

        if (!empty($matches[0])) {
            $placeholders = array_unique($matches[0]);
            foreach ($placeholders as $ph) {
                echo "- {$ph}\n";
            }
        } else {
            echo "No placeholders found\n";
        }

        echo "\n=== POTENTIAL ISSUES ===\n\n";

        // Check for common issues
        if (strpos($text, 'CANDRA') !== false) {
            echo "⚠ Original name 'CANDRA' still present\n";
        }
        if (strpos($text, '199312102020121001') !== false) {
            echo "⚠ Original NIP still present\n";
        }
        if (strpos($text, 'Pringsewu') !== false) {
            echo "⚠ Original address 'Pringsewu' still present\n";
        }
        if (strpos($text, 'MARIO') !== false) {
            echo "⚠ Original atasan name 'MARIO' still present\n";
        }
    }
}
