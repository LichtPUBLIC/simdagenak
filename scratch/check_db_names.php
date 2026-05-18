<?php
$files = [
    'd:\\laragon\\www\\siga\\lib\\server\\config.localhost.php',
    'd:\\laragon\\www\\sigadefault\\lib\\server\\config.localhost.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "=== $file ===\n";
        $content = file_get_contents($file);
        preg_match('/define\(\'DB_FW_NAME\',\s*\'([^\']+)\'\)/', $content, $matches);
        if ($matches) {
            echo "DB_FW_NAME: " . $matches[1] . "\n";
        }
    } else {
        echo "$file not found.\n";
    }
}
