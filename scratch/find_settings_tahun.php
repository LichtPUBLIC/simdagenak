<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "--- Search for SettingsTahun in modules ---\n";
    $stmt = $pdo->query("SELECT * FROM modules WHERE module = 'SettingsTahun' OR module_id = 'settings-tahun'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
