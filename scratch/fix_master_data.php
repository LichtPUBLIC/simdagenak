<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Aktifkan modul tahun yang mungkin tersembunyi
    $sql = "UPDATE modules SET onmenu = 1, active = 1 WHERE module_id IN ('data-umum-pertahun', 'tahun')";
    $count = $pdo->exec($sql);
    
    echo "Updated $count module(s).\n";
    
    // Cek apakah ada modul dengan nama 'Tahun' yang status onmenu-nya 0
    $stmt = $pdo->query("SELECT module_id, name, menu, onmenu FROM modules WHERE name LIKE '%tahun%'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
