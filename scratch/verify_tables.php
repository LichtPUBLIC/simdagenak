<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = ['data_pilah', 'data_pilah_baris', 'data_pilah_kolom', 'data_pilah_cell'];
    foreach ($tables as $t) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$t'");
        if ($stmt->rowCount() == 0) {
            echo "Table $t is MISSING\n";
        } else {
            echo "Table $t EXISTS\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
