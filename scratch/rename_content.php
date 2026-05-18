<?php
$files = [
    'd:/laragon/www/magang/modules/SettingsTahun/SettingsTahun.php',
    'd:/laragon/www/magang/modules/SettingsTahun/SettingsTahun.js',
    'd:/laragon/www/magang/modules/SettingsTahun/SettingsTahun.html'
];

foreach ($files as $f) {
    if (file_exists($f)) {
        $content = file_get_contents($f);
        $newContent = str_replace('SettingsTahun', 'settingstahun', $content);
        file_put_contents($f, $newContent);
        echo "Updated $f\n";
    } else {
        echo "File not found: $f\n";
    }
}
