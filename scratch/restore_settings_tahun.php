<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Daftarkan kembali modul SettingsTahun
    $sql = "INSERT IGNORE INTO modules (module_id, module, name, description, menu, iconcls, icon, active, onmenu, onview) 
            VALUES ('settings-tahun', 'SettingsTahun', 'Pengaturan Tahun', 'Pengaturan Tahun Berjalan', '026;Master Data/', 'angle-double-right', 'calendar', 1, 1, 'tabpanel')";
    $pdo->exec($sql);
    
    echo "Module 'SettingsTahun' registered successfully.\n";
    
    // Berikan izin akses ke superadmin (group_id 1) dan admin (group_id 2) jika ada
    $pdo->exec("INSERT IGNORE INTO group_has_modules (group_id, module_id) VALUES (1, 'settings-tahun')");
    $pdo->exec("INSERT IGNORE INTO group_has_modules (group_id, module_id) VALUES (2, 'settings-tahun')");
    
    // Daftarkan aksinya juga agar bisa dibuka
    $actions = [
        ['settings-tahun', 'ACTION_list', 'ACTION', 'list', 'List Data Tahun'],
        ['settings-tahun', 'ACTION_add', 'ACTION', 'add', 'Tambah Data Tahun'],
        ['settings-tahun', 'ACTION_edit', 'ACTION', 'edit', 'Edit Data Tahun'],
        ['settings-tahun', 'ACTION_delete', 'ACTION', 'delete', 'Hapus Data Tahun']
    ];
    
    foreach ($actions as $a) {
        $sqlAction = "INSERT IGNORE INTO actions (module_id, action_id, `option`, action, description) 
                      VALUES ('$a[0]', '$a[1]', '$a[2]', '$a[3]', '$a[4]')";
        $pdo->exec($sqlAction);
        
        // Berikan izin aksi
        $pdo->exec("INSERT IGNORE INTO group_has_actions (group_id, module_id, action_id) VALUES (1, '$a[0]', '$a[1]')");
        $pdo->exec("INSERT IGNORE INTO group_has_actions (group_id, module_id, action_id) VALUES (2, '$a[0]', '$a[1]')");
    }

    echo "Actions and Permissions registered successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
