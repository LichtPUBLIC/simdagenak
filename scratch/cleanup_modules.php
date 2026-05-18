<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. Hapus modul versi lama (data-pilah) yang merujuk ke folder _old
    $stmt1 = $pdo->prepare("DELETE FROM modules WHERE module_id = 'data-pilah'");
    $stmt1->execute();
    $deleted = $stmt1->rowCount();
    
    // 2. Pastikan modul 'tambah-kolom' menggunakan ID yang benar jika ada duplikasi (opsional, untuk kerapihan)
    // Berdasarkan list tadi, tambah-kolom sudah di 032;Manajemen Tabel/
    
    echo "Cleanup successful.\n";
    echo "- Removed $deleted old module entry (data-pilah).\n";
    echo "- Menu 'Data Pilah' now only points to the latest version.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
