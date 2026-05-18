<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Hapus data lama yang mungkin konflik
    $pdo->exec("DELETE FROM group_has_actions WHERE module_id IN ('settings-tahun', 'SettingsTahun')");
    $pdo->exec("DELETE FROM actions WHERE module_id IN ('settings-tahun', 'SettingsTahun')");
    $pdo->exec("DELETE FROM group_has_modules WHERE module_id IN ('settings-tahun', 'SettingsTahun')");
    $pdo->exec("DELETE FROM modules WHERE module_id IN ('settings-tahun', 'SettingsTahun')");
    
    // Daftarkan ulang dengan ID yang benar (SettingsTahun)
    $sqlMod = "INSERT INTO modules (module_id, module, name, description, menu, iconcls, icon, active, onmenu, onview) 
               VALUES ('SettingsTahun', 'SettingsTahun', 'Pengaturan Tahun', 'Pengaturan Tahun Berjalan', '026;Master Data/', 'angle-double-right', 'calendar', 1, 1, 'tabpanel')";
    $pdo->exec($sqlMod);
    
    // Izin modul
    $pdo->exec("INSERT INTO group_has_modules (group_id, module_id) VALUES (1, 'SettingsTahun'), (2, 'SettingsTahun')");
    
    // Daftar aksi
    $actions = [
        ['SettingsTahun', 'ACTION_list', 'ACTION', 'list', 'List Data Tahun'],
        ['SettingsTahun', 'ACTION_add', 'ACTION', 'add', 'Tambah Data Tahun'],
        ['SettingsTahun', 'ACTION_update', 'ACTION', 'update', 'Edit Data Tahun'],
        ['SettingsTahun', 'ACTION_delete', 'ACTION', 'delete', 'Hapus Data Tahun']
    ];
    
    foreach ($actions as $a) {
        $pdo->exec("INSERT INTO actions (module_id, action_id, `option`, action, description) VALUES ('$a[0]', '$a[1]', '$a[2]', '$a[3]', '$a[4]')");
        $pdo->exec("INSERT INTO group_has_actions (group_id, module_id, action_id) VALUES (1, '$a[0]', '$a[1]'), (2, '$a[0]', '$a[1]')");
    }

    echo "Module 'SettingsTahun' re-registered cleanly.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
