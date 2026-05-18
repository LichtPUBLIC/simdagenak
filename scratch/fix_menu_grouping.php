<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Kembalikan ke 026 agar masuk grup yang sama dengan yang lain
    $sqlMod = "UPDATE modules SET menu = '026;Master Data/' WHERE module_id = 'SettingsTahun'";
    $pdo->exec($sqlMod);
    
    echo "Module 'SettingsTahun' menu ID corrected back to 026.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
