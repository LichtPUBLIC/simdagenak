<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Tambahkan kolom id_instansi ke data_pilah_cell
    $sql = "ALTER TABLE data_pilah_cell ADD COLUMN id_instansi INT(11) DEFAULT 0 AFTER val";
    $pdo->exec($sql);
    
    echo "Database schema updated successfully.\n";
    echo "- Added 'id_instansi' column to 'data_pilah_cell' table.\n";
    echo "- Error 500 should be resolved now.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
