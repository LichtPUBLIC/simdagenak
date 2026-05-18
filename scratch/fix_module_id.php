<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ubah module_id agar sesuai dengan nama folder (SettingsTahun)
    // Update modules table
    $pdo->exec("UPDATE modules SET module_id = 'SettingsTahun' WHERE module_id = 'settings-tahun'");
    
    // Update group_has_modules
    $pdo->exec("UPDATE group_has_modules SET module_id = 'SettingsTahun' WHERE module_id = 'settings-tahun'");
    
    // Update actions table
    $pdo->exec("UPDATE actions SET module_id = 'SettingsTahun' WHERE module_id = 'settings-tahun'");
    
    // Update group_has_actions
    $pdo->exec("UPDATE group_has_actions SET module_id = 'SettingsTahun' WHERE module_id = 'settings-tahun'");
    
    echo "Module ID corrected to 'SettingsTahun'.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
