<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sigas', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "--- All modules with 'tahun' in ID, Class, or Name ---\n";
    $stmt = $pdo->query("SELECT * FROM modules WHERE module_id LIKE '%tahun%' OR module LIKE '%tahun%' OR name LIKE '%tahun%'");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
