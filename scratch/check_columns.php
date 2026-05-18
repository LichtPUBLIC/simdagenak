<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = ['data_pilah', 'data_pilah_baris', 'data_pilah_kolom', 'data_pilah_cell'];
    foreach ($tables as $t) {
        echo "\n--- Structure of $t ---\n";
        $stmt = $pdo->query("DESCRIBE $t");
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        print_r($cols);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
