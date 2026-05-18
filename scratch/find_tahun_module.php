<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "--- Modules related to 'tahun' ---\n";
    $stmt = $pdo->query("SELECT * FROM modules WHERE menu LIKE '%tahun%' OR module_id LIKE '%tahun%'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
