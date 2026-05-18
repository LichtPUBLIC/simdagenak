<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Cari aksi yang ada di 'data-pilah' tapi belum ada di 'datapilah'
    $sql = "INSERT IGNORE INTO actions (module_id, action_id, `option`, action, description, log)
            SELECT 'datapilah', action_id, `option`, action, description, log
            FROM (SELECT * FROM actions WHERE module_id = 'data-pilah') as old_actions";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $added = $stmt->rowCount();
    
    echo "Actions migration successful.\n";
    echo "- Added $added missing actions to 'datapilah'.\n";
    echo "- This will ensure all features in Data Pilah work correctly.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
