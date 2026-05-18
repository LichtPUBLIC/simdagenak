<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Pastikan user admin (biasanya user_id = 'admin' atau 1) punya akses langsung
    $pdo->exec("INSERT IGNORE INTO user_has_modules (user_id, module_id) VALUES ('admin', 'SettingsTahun')");
    $pdo->exec("INSERT IGNORE INTO user_has_modules (user_id, module_id) VALUES ('1', 'SettingsTahun')");
    
    echo "User specific permissions granted for 'SettingsTahun'.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
