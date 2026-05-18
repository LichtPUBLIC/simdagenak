<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "--- Details of data_pilah_kolom ---\n";
    $stmt = $pdo->query("DESCRIBE data_pilah_kolom");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
        echo "Field: {$row['Field']} | Type: {$row['Type']} | Null: {$row['Null']} | Key: {$row['Key']} | Default: " . ($row['Default'] === null ? 'NULL' : $row['Default']) . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
