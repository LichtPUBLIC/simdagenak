<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ubah semua ID ke lowercase: settingstahun
    $pdo->exec("UPDATE modules SET module_id = 'settingstahun', module = 'SettingsTahun' WHERE module_id = 'SettingsTahun'");
    $pdo->exec("UPDATE group_has_modules SET module_id = 'settingstahun' WHERE module_id = 'SettingsTahun'");
    $pdo->exec("UPDATE actions SET module_id = 'settingstahun' WHERE module_id = 'SettingsTahun'");
    $pdo->exec("UPDATE group_has_actions SET module_id = 'settingstahun' WHERE module_id = 'SettingsTahun'");
    $pdo->exec("UPDATE user_has_modules SET module_id = 'settingstahun' WHERE module_id = 'SettingsTahun'");
    
    echo "Module ID changed to lowercase 'settingstahun'.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
