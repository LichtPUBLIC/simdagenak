<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Hapus data lama
    $pdo->exec("DELETE FROM group_has_actions WHERE module_id IN ('settings-tahun', 'SettingsTahun', 'settingstahun')");
    $pdo->exec("DELETE FROM actions WHERE module_id IN ('settings-tahun', 'SettingsTahun', 'settingstahun')");
    $pdo->exec("DELETE FROM group_has_modules WHERE module_id IN ('settings-tahun', 'SettingsTahun', 'settingstahun')");
    $pdo->exec("DELETE FROM user_has_modules WHERE module_id IN ('settings-tahun', 'SettingsTahun', 'settingstahun')");
    $pdo->exec("DELETE FROM modules WHERE module_id IN ('settings-tahun', 'SettingsTahun', 'settingstahun')");
    
    // Daftarkan ulang dengan ID settingstahun (kecil)
    $sqlMod = "INSERT INTO modules (module_id, module, name, description, menu, iconcls, icon, active, onmenu, onview) 
               VALUES ('settingstahun', 'settingstahun', 'Pengaturan Tahun', 'Pengaturan Tahun Berjalan', '026;Master Data/', 'angle-double-right', 'calendar', 1, 1, 'tabpanel')";
    $pdo->exec($sqlMod);
    
    // Izin
    $pdo->exec("INSERT INTO group_has_modules (group_id, module_id) VALUES (1, 'settingstahun'), (2, 'settingstahun')");
    $pdo->exec("INSERT INTO user_has_modules (user_id, module_id) VALUES ('admin', 'settingstahun'), ('1', 'settingstahun')");
    
    // Aksi
    $actions = [
        ['settingstahun', 'ACTION_list', 'ACTION', 'list', 'List Data Tahun'],
        ['settingstahun', 'ACTION_add', 'ACTION', 'add', 'Tambah Data Tahun'],
        ['settingstahun', 'ACTION_update', 'ACTION', 'update', 'Edit Data Tahun'],
        ['settingstahun', 'ACTION_delete', 'ACTION', 'delete', 'Hapus Data Tahun']
    ];
    
    foreach ($actions as $a) {
        $pdo->exec("INSERT INTO actions (module_id, action_id, `option`, action, description) VALUES ('$a[0]', '$a[1]', '$a[2]', '$a[3]', '$a[4]')");
        $pdo->exec("INSERT INTO group_has_actions (group_id, module_id, action_id) VALUES (1, '$a[0]', '$a[1]'), (2, '$a[0]', '$a[1]')");
    }

    echo "Module 'settingstahun' (lowercase) registered completely.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
