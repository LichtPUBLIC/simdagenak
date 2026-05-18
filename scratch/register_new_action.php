<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. Daftarkan aksi baru di tabel actions
    $sql1 = "INSERT IGNORE INTO actions (module_id, action_id, `option`, action, description, log) 
             VALUES ('datapilah', 'ACTION_generateCodeKolom', 'ACTION', 'generateCodeKolom', 'Auto Generate Kode Kolom', 1)";
    $pdo->exec($sql1);
    
    // 2. Berikan izin akses ke grup (misal superadmin dan admin)
    $sql2 = "INSERT IGNORE INTO group_has_actions (group_id, module_id, action_id)
             SELECT group_id, 'datapilah', 'ACTION_generateCodeKolom'
             FROM group_has_modules 
             WHERE module_id = 'datapilah'";
    $pdo->exec($sql2);
    
    echo "Registration successful.\n";
    echo "- Registered 'ACTION_generateCodeKolom' in database.\n";
    echo "- Permissions granted to relevant groups.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
