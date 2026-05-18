<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ubah tipe data tipe_kolom dari INT menjadi VARCHAR(255)
    $sql = "ALTER TABLE data_pilah_kolom MODIFY COLUMN tipe_kolom VARCHAR(255)";
    $pdo->exec($sql);
    
    echo "Database schema updated successfully.\n";
    echo "- Changed 'tipe_kolom' to VARCHAR(255) to support text input.\n";
    echo "- Saving new columns with text type should work now.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
