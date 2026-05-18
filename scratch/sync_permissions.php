<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Masukkan izin ke datapilah untuk semua group yang tadinya punya akses ke data-pilah
    $sql = "INSERT IGNORE INTO group_has_modules (group_id, module_id)
            SELECT group_id, 'datapilah'
            FROM group_has_modules
            WHERE module_id = 'data-pilah'";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $added = $stmt->rowCount();
    
    echo "Permission synchronization successful.\n";
    echo "- Added $added group permissions to 'datapilah'.\n";
    echo "- User access should now be restored.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
