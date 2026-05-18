<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Sinkronkan izin aksi (Siapa boleh melakukan Apa di modul Mana)
    // Masukkan ke 'datapilah' semua izin aksi yang tadinya milik 'data-pilah'
    $sql = "INSERT IGNORE INTO group_has_actions (group_id, module_id, action_id)
            SELECT group_id, 'datapilah', action_id
            FROM group_has_actions
            WHERE module_id = 'data-pilah'";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $added = $stmt->rowCount();
    
    echo "Action permissions synchronization successful.\n";
    echo "- Added $added specific action permissions to 'datapilah'.\n";
    echo "- This should eliminate the 'Not Allowed' popups when loading the module.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
